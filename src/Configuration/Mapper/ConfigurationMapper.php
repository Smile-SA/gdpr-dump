<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Mapper;

use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Configuration\Definition\ConverterConfig;
use Smile\GdprDump\Configuration\Definition\ConverterConfigMap;
use Smile\GdprDump\Configuration\Definition\DumpConfig;
use Smile\GdprDump\Configuration\Definition\FakerConfig;
use Smile\GdprDump\Configuration\Definition\FilterPropagationConfig;
use Smile\GdprDump\Configuration\Definition\TableConfig;
use Smile\GdprDump\Configuration\Definition\TableConfigMap;
use Smile\GdprDump\Configuration\Exception\UnexpectedValueException;
use Smile\GdprDump\Util\Arrays;

final class ConfigurationMapper
{
    public function __construct(private SortOrderMapper $sortOrderMapper)
    {
    }

    /**
     * Create a Configuration object from the provided data.
     *
     * The mapping will likely fail if the data was not validated with the JSON schema validator.
     */
    public function fromArray(array $source): Configuration
    {
        $configuration = new Configuration();

        foreach ($source as $key => $value) {
            match ($key) {
                'database' => $configuration->setConnectionParams($this->buildConnectionParams($value)),
                'dump' => $configuration->setDumpSettings($this->buildDumpConfig($value)),
                'faker' => $configuration->setFakerConfig($this->buildFakerConfig($value)),
                'filter_propagation' => $configuration->setFilterPropagationConfig(
                    $this->buildFilterPropagationConfig($value)
                ),
                'tables_blacklist' => $configuration->setExcludedTables($value),
                'tables_whitelist' => $configuration->setIncludedTables($value),
                'tables' => $configuration->setTableConfigs(
                    new TableConfigMap(array_map(fn (array $table) => $this->buildTableConfig($table), $value))
                ),
                'strict_schema' => $configuration->setStrictSchema($value),
                'variables' => $configuration->setSqlVariables($value),
                'version', 'if_version' => null, // these parameters were only useful for parsing and validation
                'requires_version' => null, // deprecated parameter
                default => throw new UnexpectedValueException(sprintf('Unsupported config property "%s".', $key)),
            };
        }

        return $configuration;
    }

    /**
     * Create the connection params (doctrine format).
     */
    private function buildConnectionParams(array $connectionParams): array
    {
        // Remap connection params so that they match doctrine
        $mapping = ['name' => 'dbname', 'driver_options' => 'driverOptions'];
        $connectionParams = Arrays::mapKeys(
            $connectionParams,
            fn (string $key) => array_key_exists($key, $mapping) ? $mapping[$key] : $key
        );

        return $connectionParams;
    }

    /**
     * Create a DumpConfig object from the provided data.
     */
    private function buildDumpConfig(array $source): DumpConfig
    {
        $configuration = new DumpConfig();

        foreach ($source as $key => $value) {
            match ($key) {
                'add_drop_database' => $configuration->setAddDropDatabase($value),
                'add_drop_table' => $configuration->setAddDropTable($value),
                'add_drop_trigger' => $configuration->setAddDropTrigger($value),
                'add_locks' => $configuration->setAddLocks($value),
                'complete_insert' => $configuration->setCompleteInsert($value),
                'compress' => $configuration->setCompress($value),
                'default_character_set' => $configuration->setDefaultCharacterSet($value),
                'disable_keys' => $configuration->setDisableKeys($value),
                'events' => $configuration->setEvents($value),
                'extended_insert' => $configuration->setExtendedInsert($value),
                'hex_blob' => $configuration->setHexBlob($value),
                'init_commands' => $configuration->setInitCommands($value),
                'insert_ignore' => $configuration->setInsertIgnore($value),
                'lock_tables' => $configuration->setLockTables($value),
                'net_buffer_length' => $configuration->setNetBufferLength($value),
                'no_autocommit' => $configuration->setNoAutocommit($value),
                'no_create_info' => $configuration->setNoCreateInfo($value),
                'output' => $configuration->setOutput($value),
                'routines' => $configuration->setRoutines($value),
                'single_transaction' => $configuration->setSingleTransaction($value),
                'skip_comments' => $configuration->setSkipComments($value),
                'skip_definer' => $configuration->setSkipDefiner($value),
                'skip_dump_date' => $configuration->setSkipDumpDate($value),
                'skip_triggers' => $configuration->setSkipTriggers($value),
                'skip_tz_utc' => $configuration->setSkipTzUtc($value),
                default => throw new UnexpectedValueException(sprintf('Unsupported dump property "%s".', $key)),
            };
        }

        return $configuration;
    }

    /**
     * Create a FakerConfig object from the provided data.
     */
    private function buildFakerConfig(array $source): FakerConfig
    {
        $configuration = new FakerConfig();

        foreach ($source as $key => $value) {
            match ($key) {
                'locale' => $configuration->setLocale((string) $value),
                default => throw new UnexpectedValueException(sprintf('Unsupported faker property "%s".', $key)),
            };
        }

        return $configuration;
    }

    /**
     * Create a FilterPropagationConfig object from the provided data.
     */
    private function buildFilterPropagationConfig(array $source): FilterPropagationConfig
    {
        $configuration = new FilterPropagationConfig();

        foreach ($source as $key => $value) {
            match ($key) {
                'enabled' => $configuration->setEnabled($value),
                'ignored_foreign_keys' => $configuration->setIgnoredForeignKeys($value),
                default => throw new UnexpectedValueException(sprintf('Unsupported propagation property "%s".', $key)),
            };
        }

        return $configuration;
    }

    /**
     * Create a TableConfig object from the provided data.
     */
    private function buildTableConfig(array $source): TableConfig
    {
        $configuration = new TableConfig();

        foreach ($source as $key => $value) {
            match ($key) {
                'truncate' => $configuration->setTruncate($value),
                'where' => $configuration->setWhere($value),
                'limit' => $configuration->setLimit($value),
                'order_by' => $configuration->setSortOrders($this->sortOrderMapper->fromString($value)),
                'skip_conversion_if' => $configuration->setSkipCondition($value),
                'converters' => $configuration->setConverterConfigs(
                    new ConverterConfigMap(
                        array_map(fn (array $converter) => $this->buildConverterConfig($converter), $value)
                    )
                ),
                'filters' => throw new UnexpectedValueException(
                    'The table property "filters" is no longer supported, use "where" instead.'
                ),
                default => throw new UnexpectedValueException(sprintf('Unsupported table property "%s".', $key)),
            };
        }

        return $configuration;
    }

    /**
     * Create a ConverterConfig object from the provided data.
     */
    private function buildConverterConfig(array $source): ConverterConfig
    {
        if (array_key_exists('disabled', $source) && $source['disabled']) {
            // Replace the converter with a converter that doesn't do anything
            $source = ['converter' => 'noop'];
        }

        if (!array_key_exists('converter', $source)) {
            throw new UnexpectedValueException(sprintf('The converter name is required.'));
        }

        $configuration = new ConverterConfig($source['converter']);

        foreach ($source as $key => $value) {
            match ($key) {
                'parameters' => $configuration->setParameters($this->buildParameters($value)),
                'condition' => $configuration->setCondition($value),
                'cache_key' => $configuration->setCacheKey($value),
                'unique' => $configuration->setUnique($value),
                'converter', 'disabled' => null, // already processed before this loop
                default => throw new UnexpectedValueException(
                    sprintf('Unsupported converter property "%s".', $key)
                ),
            };
        }

        return $configuration;
    }

    /**
     * Buld converters recursively in the specified parameters array.
     */
    private function buildParameters(array $parameters): array
    {
        if (array_key_exists('converter', $parameters)) {
            // Convert `converter` params to a ConverterConfig object
            $parameters['converter'] = $this->buildConverterConfig($parameters['converter']);
        }

        if (array_key_exists('converters', $parameters)) {
            // Convert `converters` params to an array of ConverterConfig objects
            $parameters['converters'] = array_map(
                fn (array $converter) => $this->buildConverterConfig($converter),
                $parameters['converters']
            );
        }

        return $parameters;
    }
}
