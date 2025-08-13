<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config;

use Smile\GdprDump\Config\Exception\MappingException;
use Smile\GdprDump\Config\Loader\Container;
use Smile\GdprDump\Dumper\Config\Validation\QueryValidator;

final class DumperConfig extends Container
{
    public function isStrictSchema(): bool
    {
        return $this->get('strict_schema', false);
    }

    public function setStrictSchema(bool $strictSchema): self
    {
        return $this->set('strict_schema', $strictSchema);
    }

    /**
     * @return array<string, array|scalar>
     */
    public function getConnectionParams(): array
    {
        return $this->get('database', []);
    }

    /**
     * @param array<string, mixed> $params
     */
    public function setConnectionParams(array $params): self
    {
        return $this->set('database', $params);
    }

    public function getDumpOutput(): string
    {
        return $this->get('output');
    }

    public function setDumpOutput(string $output): self
    {
        return $this->set('output', $output);
    }

    public function getDumpSettings(): DumpConfig
    {
        return $this->get('dump', []);
    }

    public function setDumpSettings(DumpConfig $dumpConfig): self
    {
        // Validate init commands
        $queryValidator = new QueryValidator(['set']);
        foreach ($dumpConfig->getInitCommands() as $query) {
            $queryValidator->validate($query);
        }

        return $this->set('dump', $dumpConfig);
    }

    /**
     * @return array<string, TableConfig>
     */
    public function getTablesConfig(): array
    {
        return $this->get('tables', []);
    }

    /**
     * @param array<string, TableConfig> $tablesConfig
     */
    public function setTablesConfig(array $tablesConfig): self
    {
        return $this->set('tables', $tablesConfig);
    }

    /**
     * @return string[]
     */
    public function getIncludedTables(): array
    {
        return $this->get('tables_whitelist', []);
    }

    /**
     * @param string[] $tableNames
     */
    public function setIncludedTables(array $tableNames): self
    {
        return $this->set('tables_whitelist', $tableNames);
    }

    /**
     * @return string[]
     */
    public function getExcludedTables(): array
    {
        return $this->get('tables_blacklist', []);
    }

    /**
     * @param array<string, string> $tableNames
     */
    public function setExcludedTables(array $tableNames): self
    {
        return $this->set('tables_blacklist', $tableNames);
    }

    /**
     * @return array<string, string>
     */
    public function getVariables(): array
    {
        return $this->get('variables', []);
    }

    /**
     * @param array<string> $variables
     */
    public function setVariables(array $variables): self
    {
        // Validate SQL queries
        $queryValidator = new QueryValidator(['select']);
        foreach ($variables as $query) {
            $queryValidator->validate($query);
        }

        return $this->set('variables', $variables);
    }

    public function getFilterPropagationConfig(): FilterPropagationConfig
    {
        return $this->get('filter_propagation');
    }

    public function setFilterPropagationConfig(FilterPropagationConfig $filterPropagation): self
    {
        return $this->set('filter_propagation', $filterPropagation);
    }

    public function getFakerConfig(): FakerConfig
    {
        return $this->get('faker');
    }

    public function setFakerConfig(FakerConfig $fakerConfig): self
    {
        return $this->set('faker', $fakerConfig);
    }

    public function fromArray(array $items): self
    {
        $this->items = [];

        foreach (['requires_version', 'version'] as $remove) {
            if (array_key_exists($remove, $items)) {
                unset($items[$remove]);
            }
        }

        if (array_key_exists('dump', $items) && array_key_exists('output', $items['dump'])) {
            $this->setDumpOutput($items['dump']['output']);
        }

        foreach ($items as $property => $value) {
            match ($property) {
                'database' => $this->setConnectionParams($value),
                'dump' => $this->setDumpSettings((new DumpConfig())->fromArray($value)),
                'faker' => $this->setFakerConfig((new FakerConfig())->fromArray($value)),
                'filter_propagation' => $this->setFilterPropagationConfig(
                    (new FilterPropagationConfig())->fromArray($value)
                ),
                'tables_blacklist' => $this->setExcludedTables($value),
                'tables_whitelist' => $this->setIncludedTables($value),
                'tables' => $this->setTablesConfig(
                    array_map(
                        fn (array $table) => (new TableConfig())->fromArray($table),
                        $value
                    )
                ),
                'strict_schema' => $this->setStrictSchema($value),
                'variables' => $this->setVariables($value),
                default => throw new MappingException(sprintf('Unsupported config property "%s".', $property)),
            };
        }

        return $this;
    }
}
