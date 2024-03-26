<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config\Definition;

use Smile\GdprDump\Dumper\Config\Definition\Table\SortOrder;
use Smile\GdprDump\Dumper\Config\Validation\ValidationException;
use Smile\GdprDump\Dumper\Config\Validation\WhereExprValidator;
use UnexpectedValueException;

class TableConfig
{
    private string $name;
    private ?string $where = null;
    private ?int $limit = null;
    private array $converters = [];
    private string $skipCondition = '';

    /**
     * @var SortOrder[]
     */
    private array $sortOrders = [];

    public function __construct(string $tableName, array $tableConfig)
    {
        $this->name = $tableName;
        $this->prepareConfig($tableConfig);
    }

    /**
     * Get the table name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the where condition.
     */
    public function getWhereCondition(): ?string
    {
        return $this->where;
    }

    /**
     * Get the sort orders.
     *
     * @return SortOrder[]
     */
    public function getSortOrders(): array
    {
        return $this->sortOrders;
    }

    /**
     * Get the table limit.
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * Get the converter definitions of a table.
     */
    public function getConverters(): array
    {
        return $this->converters;
    }

    /**
     * Check if there is data to filter (with `where` param).
     */
    public function hasWhereCondition(): bool
    {
        return $this->where !== null;
    }

    /**
     * Check if the table data must be sorted.
     */
    public function hasSortOrder(): bool
    {
        return !empty($this->sortOrders);
    }

    /**
     * Check if a limit is defined.
     */
    public function hasLimit(): bool
    {
        return $this->limit !== null;
    }

    /**
     * Get the conversion skip condition.
     * Data conversion is disabled when the condition evaluates to true.
     */
    public function getSkipCondition(): string
    {
        return $this->skipCondition;
    }

    /**
     * Prepare the table config.
     */
    private function prepareConfig(array $tableData): void
    {
        $this->prepareFilters($tableData);
        $this->prepareSortOrder($tableData);
        $this->prepareLimit($tableData);
        $this->prepareConverters($tableData);
    }

    /**
     * Prepare the table filters.
     *
     * @throws ValidationException
     */
    private function prepareFilters(array $tableData): void
    {
        if (array_key_exists('filters', $tableData)) {
            throw new ValidationException(
                'The property "filters" is no longer supported. Use "where" instead.'
            );
        }

        $whereCondition = (string) ($tableData['where'] ?? '');
        if ($whereCondition !== '') {
            $whereExprValidator = new WhereExprValidator();
            $whereExprValidator->validate($whereCondition);
            $this->where = $whereCondition;
        }
    }

    /**
     * Prepare the table sort order.
     *
     * @throws UnexpectedValueException
     */
    private function prepareSortOrder(array $tableData): void
    {
        $orderBy = (string) ($tableData['order_by'] ?? '');
        if ($orderBy === '') {
            return;
        }

        $orders = explode(',', $orderBy);
        $orders = array_map('trim', $orders);

        foreach ($orders as $order) {
            $parts = explode(' ', $order);

            if (count($parts) > 2) {
                throw new UnexpectedValueException(sprintf('The sort order "%s" is not valid.', $order));
            }

            $column = $parts[0];
            $direction = $parts[1] ?? SortOrder::DIRECTION_ASC;

            $this->sortOrders[] = new SortOrder($column, $direction);
        }
    }

    /**
     * Prepare the table limit.
     */
    private function prepareLimit(array $tableData): void
    {
        if (isset($tableData['limit']) && $tableData['limit'] > 0) {
            $this->limit = (int) $tableData['limit'];
        }

        if (isset($tableData['truncate']) && $tableData['truncate']) {
            $this->limit = 0;
        }
    }

    /**
     * Prepare the table converters.
     */
    private function prepareConverters(array $tableData): void
    {
        if (isset($tableData['converters'])) {
            foreach ($tableData['converters'] as $column => $converterData) {
                // Ignore disabled converters
                if (array_key_exists('disabled', $converterData) && $converterData['disabled']) {
                    break;
                }

                // Converter data will be validated by the factory during the object creation
                $this->converters[$column] = $converterData;
            }
        }

        $skipCondition = (string) ($tableData['skip_conversion_if'] ?? '');
        if ($skipCondition !== '') {
            $this->skipCondition = $skipCondition;
        }
    }
}
