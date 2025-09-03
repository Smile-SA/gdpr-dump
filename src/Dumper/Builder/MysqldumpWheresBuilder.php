<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Builder;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Database\Database;
use Smile\GdprDump\Database\Metadata\DatabaseMetadata;
use Smile\GdprDump\Database\Metadata\Definition\ForeignKey;
use Smile\GdprDump\Database\TableDependencyResolver;

final class MysqldumpWheresBuilder
{
    private Configuration $configuration;
    private Connection $connection;
    private DatabaseMetadata $metadata;

    /**
     * Define the filters to apply on the tables.
     */
    public function build(Configuration $configuration, Database $database): array
    {
        $this->configuration = $configuration;
        $this->connection = $database->getConnection();
        $this->metadata = $database->getMetadata();

        try {
            $result = $this->buildTablesWhere();
        } finally {
            unset($this->configuration);
            unset($this->connection);
            unset($this->metadata);
        }

        return $result;
    }

    /**
     * Get the filters to apply on each table.
     */
    private function buildTablesWhere(): array
    {
        // Get the tables to sort/filter
        $tablesToFilter = $this->configuration->getTableConfigs()->getTablesToFilter();
        $tablesToSort = $this->configuration->getTableConfigs()->getTablesToSort();

        // Do nothing if there is no sort order/filter defined in the configuration
        if (!$tablesToFilter && !$tablesToSort) {
            return [];
        }

        $dependencies = [];
        $tableWheres = [];

        // If recursive filters are enabled, tables to query must contain
        // all tables that depend on the tables that have filters/sort order
        if ($this->configuration->getFilterPropagationConfig()->isEnabled()) {
            $ignoredForeignKeys = $this->configuration->getFilterPropagationConfig()->getIgnoredForeignKeys();
            $dependencyResolver = new TableDependencyResolver($this->metadata, $ignoredForeignKeys);
            $dependencies = $dependencyResolver->getDependencies($tablesToFilter);
        }

        // Tables to query are:
        // - tables with filters or sort orders declared in the config
        // - tables that depend on the tables to filter
        $tablesToQuery = array_unique(array_merge(array_keys($dependencies), $tablesToFilter, $tablesToSort));

        foreach ($tablesToQuery as $tableName) {
            // Create the query that will contain a combination of filter / sort order / limit
            $queryBuilder = $this->createQueryBuilder($tableName);

            // Add where conditions on the parent tables that also have active filters
            if (
                $this->configuration->getFilterPropagationConfig()->isEnabled()
                && $queryBuilder->getMaxResults() !== 0
                && array_key_exists($tableName, $dependencies)
            ) {
                $this->addDependentFilter($tableName, $queryBuilder, $dependencies);
            }

            // Convert the query to SQL, starting from the "WHERE" clause
            $tableWheres[$tableName] = $this->getWhereSql($queryBuilder);
        }

        // Sort by table name (easier to debug)
        ksort($tableWheres);

        return $tableWheres;
    }

    /**
     * Add a filter on the dependent tables, by iterating on all foreign keys of the table.
     *
     * Example with the following dependencies:
     * - "addresses": foreign key to "customers" (customer_id field)
     * - "customers": foreign key to "stores" (store_id field)
     *
     * With $tableName = 'addresses', this function will add the following filter to the query:
     *
     * ```
     * WHERE `customer_id` IN (
     *     SELECT * FROM (SELECT `customer_id` FROM `customers` WHERE ... AND `store_id` IN (
     *         SELECT * FROM (SELECT `store_id` FROM `stores` WHERE ...) `sub2`
     *     )) `sub1`
     * );
     * ```
     *
     * Where `...` are the filters defined in the YAML config (if any).
     *
     * Internal parameters:
     * - $processedTables: used to detect cyclic dependencies and stop the recursion
     * - $subQueryCount: used to generate unique query names
     */
    private function addDependentFilter(
        string $tableName,
        QueryBuilder $queryBuilder,
        array $dependencies,
        array $processedTables = [],
        int &$subQueryCount = 0,
    ): void {
        if (!$processedTables) {
            // Initialize $processedTables with the table name that was initially passed to the function
            // (otherwise, if this table had a cyclic dependency on itself, it would not be detected)
            $processedTables[$tableName] = true;
        }

        /** @var ForeignKey $dependency */
        foreach ($dependencies[$tableName] as $dependency) {
            $tableName = $dependency->getForeignTableName();

            // Stop recursion when a cyclic dependency is detected
            if (array_key_exists($tableName, $processedTables)) {
                continue;
            }

            $processedTables[$tableName] = true;

            $subQuery = $this->createQueryBuilder($tableName);
            $subQuery->select($this->getColumnsSql($dependency->getForeignColumns()));

            // Recursively add condition on parent tables
            if ($subQuery->getMaxResults() !== 0 && array_key_exists($tableName, $dependencies)) {
                $this->addDependentFilter($tableName, $subQuery, $dependencies, $processedTables, $subQueryCount);
            }

            // Prepare the condition data
            $subQueryCount++;
            $subQueryName = $this->connection->quoteIdentifier('sub_' . $subQueryCount);
            $columnsSql = $this->getColumnsSql($dependency->getLocalColumns(), true);

            // Filter on the foreign keys
            // (wrap the sub query in a SELECT * FROM (...),
            // because otherwise a sub query cannot declare the LIMIT clause)
            $expr = $queryBuilder
                ->expr()
                ->comparison($columnsSql, 'IN', '(SELECT * FROM (' . $subQuery . ') ' . $subQueryName . ')');

            // Allow null values
            foreach ($dependency->getLocalColumns() as $column) {
                $expr = $queryBuilder
                    ->expr()
                    ->or($expr, $subQuery->expr()->isNull($this->connection->quoteIdentifier($column)));
            }

            $queryBuilder->andWhere($expr);
        }
    }

    /**
     * Create a query builder that applies the configuration of the specified table.
     */
    private function createQueryBuilder(string $tableName): QueryBuilder
    {
        // Create the query builder
        $queryBuilder = $this->connection->createQueryBuilder();

        // Select all columns of the table
        $queryBuilder->select('*')
            ->from($this->connection->quoteIdentifier($tableName));

        $tableConfig = $this->configuration->getTableConfigs()->get($tableName);
        if (!$tableConfig) {
            // Table doesn't exist, skip
            return $queryBuilder;
        }

        // Apply where condition (wrap the condition with brackets to prevent SQL injection)
        if ($tableConfig->getWhere() !== '') {
            $queryBuilder->andWhere(sprintf('(%s)', $tableConfig->getWhere()));
        }

        // Apply sort orders
        foreach ($tableConfig->getSortOrders() as $sortOrder) {
            $queryBuilder->addOrderBy(
                $this->connection->quoteIdentifier($sortOrder->getColumn()),
                $sortOrder->getDirection()->toString()
            );
        }

        // Apply limit
        if ($tableConfig->getLimit() !== null) {
            $queryBuilder->setMaxResults($tableConfig->getLimit());
        }

        return $queryBuilder;
    }

    /**
     * Get the SQL query that represents a list of columns.
     */
    private function getColumnsSql(array $columns, bool $enclose = false): string
    {
        foreach ($columns as $index => $column) {
            $columns[$index] = $this->connection->quoteIdentifier($column);
        }

        $result = implode(',', $columns);

        if (count($columns) > 1 && $enclose) {
            // Enclose selected columns with parentheses
            $result = '(' . $result . ')';
        }

        return $result;
    }

    /**
     * Get the query as SQL, starting from the WHERE clause.
     */
    private function getWhereSql(QueryBuilder $queryBuilder): string
    {
        $wherePart = $queryBuilder->getQueryPart('where');
        if ($wherePart === null) {
            $queryBuilder->where(1);
        }

        $sql = $queryBuilder->getSQL();

        return substr($sql, strpos($sql, ' WHERE ') + 7);
    }
}
