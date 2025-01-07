<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config;

use Smile\GdprDump\Dumper\Config\Definition\FakerSettings;
use Smile\GdprDump\Dumper\Config\Definition\FilterPropagationSettings;
use Smile\GdprDump\Dumper\Config\Definition\TableConfigCollection;

interface DumperConfigInterface
{
    /**
     * Get the dump output.
     */
    public function getDumpOutput(): string;

    /**
     * Get dump settings.
     */
    public function getDumpSettings(): array;

    /**
     * Get faker settings.
     */
    public function getFakerSettings(): FakerSettings;

    /**
     * Get filter propagation settings.
     */
    public function getFilterPropagationSettings(): FilterPropagationSettings;

    /**
     * Get the tables configuration (filters, orders, limits).
     */
    public function getTablesConfig(): TableConfigCollection;

    /**
     * Get the SQL queries to run.
     *
     * The result of each query will then be injected into user-defined variables.
     * Array keys are the variable names, array values are the database queries.
     *
     * @return string[]
     */
    public function getVarQueries(): array;

    /**
     * Get the tables to include.
     *
     * @return string[]
     */
    public function getIncludedTables(): array;

    /**
     * Get the tables to exclude.
     *
     * @return string[]
     */
    public function getExcludedTables(): array;

    /**
     * Get the tables to truncate (only the structure is included in the dump file, not the data).
     *
     * @return string[]
     */
    public function getTablesToTruncate(): array;

    /**
     * Get the names of the tables to filter.
     *
     * @return string[]
     */
    public function getTablesToFilter(): array;

    /**
     * Get the names of the tables to sort.
     *
     * @return string[]
     */
    public function getTablesToSort(): array;
}
