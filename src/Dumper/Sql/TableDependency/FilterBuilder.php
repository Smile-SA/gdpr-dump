<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Dumper\Sql\TableDependency;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Smile\Anonymizer\Dumper\Sql\Config\Table\Filter\Filter;
use Smile\Anonymizer\Dumper\Sql\DumperConfig;

class FilterBuilder
{
    /**
     * @var DumperConfig
     */
    private $config;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param DumperConfig $config
     * @param Connection $connection
     */
    public function __construct(DumperConfig $config, Connection $connection)
    {
        $this->config = $config;
        $this->connection = $connection;
    }

    /**
     * Get the filters to apply on each table.
     *
     * @return array
     */
    public function getTableFilters(): array
    {
        $tablesToFilter = $this->config->getTablesToFilter();
        $tablesToSort = $this->config->getTablesToSort();

        if (empty($tablesToFilter) && empty($tablesToSort)) {
            return [];
        }

        $dependencyTree = new DependencyTree($this->connection);

        // Get the foreign keys of each table that depends on the filters that were declared in the configuration
        $dependencies = $dependencyTree->getTablesDependencies($tablesToFilter);

        // Use these foreign keys to build the WHERE condition of each table
        $tablesWheres = [];

        $tablesToQuery = array_unique(array_merge(array_keys($dependencies), $tablesToSort));

        foreach ($tablesToQuery as $tableName) {
            // Create the query that will contain a combination of filter / sort order / limit
            $queryBuilder = $this->createQueryBuilder($tableName);

            // Add where conditions on the parent tables that also have active filters
            if ($queryBuilder->getMaxResults() !== 0 && array_key_exists($tableName, $dependencies)) {
                $this->addDependentFilter($tableName, $queryBuilder, $dependencies);
            }

            // Convert the query to SQL, starting from the "WHERE" clause
            $sql = $this->getQueryString($queryBuilder);
            $tablesWheres[$tableName] = $sql;
        }

        return $tablesWheres;
    }

    /**
     * Add a filter on the dependent tables.
     *
     * @param string $tableName
     * @param QueryBuilder $queryBuilder
     * @param array $dependencies
     * @param int $subQueryCount
     */
    private function addDependentFilter(
        string $tableName,
        QueryBuilder $queryBuilder,
        &$dependencies,
        &$subQueryCount = 0
    ) {
        if (!array_key_exists($tableName, $dependencies)) {
            throw new \UnexpectedValueException(sprintf('The table dependency "%s" was not found.', $tableName));
        }

        /** @var ForeignKeyConstraint $dependency */
        foreach ($dependencies[$tableName] as $dependency) {
            $qb = $this->createQueryBuilder($dependency->getForeignTableName());
            $qb->select($this->getColumnsSql($dependency->getForeignColumns()));

            // Recursively add condition on parent tables
            if ($qb->getMaxResults() !== 0) {
                $this->addDependentFilter($dependency->getForeignTableName(), $qb, $dependencies, $subQueryCount);
            }

            // Prepare the condition data
            $subQueryCount++;
            $subQueryName = $this->connection->quoteIdentifier('sub_' . $subQueryCount);
            $columnsSql = $this->getColumnsSql($dependency->getLocalColumns());

            // Filter on the foreign keys
            // (wrap the sub query in a SELECT * FROM (...),
            // because otherwise a sub query cannot declare the LIMIT clause)
            $expr = $queryBuilder
                ->expr()
                ->comparison($columnsSql, 'IN', '(SELECT * FROM (' . $qb. ') ' . $subQueryName . ')');

            // Allow null values
            foreach ($dependency->getLocalColumns() as $column) {
                $expr = $queryBuilder
                    ->expr()
                    ->orX($expr, $qb->expr()->isNull($this->connection->quoteIdentifier($column)));
            }

            $queryBuilder->andWhere($expr);
        }
    }

    /**
     * Create a query builder for the specified table.
     *
     * @param string $tableName
     * @return QueryBuilder
     */
    private function createQueryBuilder(string $tableName): QueryBuilder
    {
        $tableConfig = $this->config->getTableConfig($tableName);

        // Initialize the query
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('*')
            ->from($this->connection->quoteIdentifier($tableName));

        if (!$tableConfig) {
            return $queryBuilder;
        }

        // Filters
        foreach ($tableConfig->getFilters() as $filter) {
            $value = $this->getFilterValue($filter);

            $whereExpr = call_user_func_array(
                [$queryBuilder->expr(), $filter->getOperator()],
                [$this->connection->quoteIdentifier($filter->getColumn()), $value]
            );

            $queryBuilder->andWhere($whereExpr);
        }

        // Sort orders
        foreach ($tableConfig->getSortOrders() as $sortOrder) {
            $queryBuilder->addOrderBy(
                $this->connection->quoteIdentifier($sortOrder->getColumn()),
                $sortOrder->getDirection()
            );
        }

        // Limit
        $limit = $tableConfig->isDataDumped() ? $tableConfig->getLimit() : 0;
        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder;
    }

    /**
     * Get the SQL query that represents a list of columns.
     *
     * @param array $columns
     * @return string
     */
    private function getColumnsSql(array $columns): string
    {
        foreach ($columns as $index => $column) {
            $columns[$index] = $this->connection->quoteIdentifier($column);
        }

        $result = implode(',', $columns);

        if (count($columns) > 1) {
            $result = '(' . $result . ')';
        }

        return $result;
    }

    /**
     * Get a filter value.
     *
     * @param Filter $filter
     * @return mixed
     */
    private function getFilterValue(Filter $filter)
    {
        $value = $filter->getValue();

        if (in_array($filter->getOperator(), [Filter::OPERATOR_IN, Filter::OPERATOR_NOT_IN])) {
            if (!is_array($value)) {
                throw new \UnexpectedValueException('The IN operator requires array values.');
            }

            foreach ($value as $k => $v) {
                $value[$k] = $this->quoteValue($v);
            }

            return $value;
        }

        return $this->quoteValue($value);
    }

    /**
     * Quote a value so that it can be safely injected in a SQL query.
     * (we can't use query params because Mysqldump library doesn't allow it)
     *
     * @param mixed $value
     * @return mixed
     */
    private function quoteValue($value)
    {
        if ($value !== null && !is_scalar($value)) {
            throw new \UnexpectedValueException('Non-scalar values can not be used in filters.');
        }

        if (is_bool($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            return strpos($value, 'expr:') === 0 ? ltrim(substr($value, 5)) : $this->connection->quote($value);
        }

        return $value;
    }

    /**
     * Get the query as SQL, starting from the WHERE clause.
     *
     * @param QueryBuilder $queryBuilder
     * @return string
     */
    private function getQueryString(QueryBuilder $queryBuilder): string
    {
        $wherePart = $queryBuilder->getQueryPart('where');

        if (empty($wherePart)) {
            $queryBuilder->where(1);
        }

        $sql = $queryBuilder->getSQL();

        return substr($sql, strpos($sql, ' WHERE ') + 7);
    }
}
