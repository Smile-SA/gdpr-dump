<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration;

use Smile\GdprDump\Configuration\Definition\ConverterConfig;
use Smile\GdprDump\Configuration\Definition\DumpConfig;
use Smile\GdprDump\Configuration\Definition\FakerConfig;
use Smile\GdprDump\Configuration\Definition\FilterPropagationConfig;
use Smile\GdprDump\Configuration\Definition\Table\SortOrder;
use Smile\GdprDump\Configuration\Definition\TableConfig;
use Smile\GdprDump\Configuration\Resource\Resource;
use Smile\GdprDump\Configuration\Validator\JsonSchemaValidator;
use Smile\GdprDump\Configuration\Exception\ConfigurationException;
use Smile\GdprDump\Configuration\Exception\InvalidConfigException;
use Smile\GdprDump\Util\Arrays;
use Smile\GdprDump\Util\Objects;

final class ConfigurationBuilder
{
    /**
     * @var Resource[]
     */
    private array $resources = [];

    public function __construct(
        private ConfigurationParser $configurationParser,
        private JsonSchemaValidator $schemaValidator,
    ) {
    }

    /**
     * Add a resource to the builder.
     */
    public function addResource(Resource $resource): self
    {
        $this->resources[] = $resource;

        return $this;
    }

    /**
     * Build the configuration from the specified resources.
     *
     * @throws ConfigurationException
     */
    public function build(): Configuration
    {
        $configData = $this->configurationParser->parse(...$this->resources);
        $this->schemaValidator->validate($configData);

        return $this->buildConfiguration(Objects::toArray($configData));
    }

    /**
     * Create a Configuration object from the provided data.
     */
    public function buildConfiguration(array $source): Configuration
    {
        $configuration = new Configuration();

        foreach ($source as $property => $value) {
            match ($property) {
                'database' => $configuration->setConnectionParams($this->buildConnectionParams($value)),
                'dump' => $configuration->setDumpSettings($this->buildDumpConfig($value)),
                'faker' => $configuration->setFakerConfig($this->buildFakerConfig($value)),
                'filter_propagation' => $configuration->setFilterPropagationConfig($this->buildFilterPropagationConfig($value)),
                'tables_blacklist' => $configuration->setExcludedTables($value),
                'tables_whitelist' => $configuration->setIncludedTables($value),
                'tables' => $configuration->setTablesConfig(
                    array_map(fn (array $table) => $this->buildTableConfig($table), $value)
                ),
                'strict_schema' => $configuration->setStrictSchema($value),
                'variables' => $configuration->setVarQueries($value),
                'requires_version', 'version', 'if_version' => null, // not mapped
                default => throw new InvalidConfigException(sprintf('Unsupported config property "%s".', $property)),
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

        foreach ($source as $property => $value) {
            $items[$property] = match ($property) {
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
                default => throw new InvalidConfigException(sprintf('Unsupported dump property "%s".', $property)),
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

        foreach ($source as $property => $value) {
            match ($property) {
                'locale' => $configuration->setLocale($value),
                default => throw new InvalidConfigException(sprintf('Unsupported faker property "%s".', $property)),
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

        foreach ($source as $property => $value) {
            match ($property) {
                'enabled' => $configuration->setEnabled($value),
                'ignored_foreign_keys' => $configuration->setIgnoredForeignKeys($value),
                default => throw new InvalidConfigException(sprintf('Unsupported propagation property "%s".', $property)),
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

        foreach ($source as $property => $value) {
            match ($property) {
                'truncate' => $configuration->setTruncate($value),
                'where' => $configuration->setWhere($value),
                'limit' => $configuration->setLimit($value),
                'order_by' => $configuration->setSortOrders($this->buildSortOrders($value)),
                'skip_conversion_if' => $configuration->setSkipCondition($value),
                'converters' => $configuration->setConvertersConfig($this->buildConvertersConfig($value)),
                'filters' => throw new InvalidConfigException('The table property "filters" is no longer supported, use "where" instead.'),
                default => throw new InvalidConfigException(sprintf('Unsupported table property "%s".', $property)),
            };
        }

        return $configuration;
    }

    /**
     * Create an array of ConverterConfig objects from the provided data.
     */
    private function buildConvertersConfig(array $convertersData): array
    {
        return array_map(fn (array $converter) => $this->buildConverterConfig($converter), $convertersData);
    }

    /**
     * Create a ConverterConfig object from the provided data.
     */
    public function buildConverterConfig(array $source): ConverterConfig
    {
        if (array_key_exists('disabled', $source) && $source['disabled']) {
            // Replace the converter with a converter that doesn't do anything
            $source = ['converter' => 'disabled'];
        }

        $configuration = new ConverterConfig($source['converter']);

        foreach ($source as $property => $value) {
            match ($property) {
                'parameters' => $configuration->setParameters($this->buildParameters($value)),
                'condition' => $configuration->setCondition($value),
                'cache_key' => $configuration->setCacheKey($value),
                'unique' => $configuration->setUnique($value),
                'converter', 'disabled' => null,
                default => throw new InvalidConfigException(sprintf('Unsupported converter property "%s".', $property)),
            };
        }

        return $configuration;
    }

    /**
     * Buld converters recursively in the specified parameters array.
     */
    private function buildParameters(array $parameters): array
    {
        foreach ($parameters as $parameter => $value) {
            if ($parameter === 'converter') {
                $parameters[$parameter] = $this->buildConverterConfig($value);
            } elseif ($parameter === 'converters') {
                $parameters[$parameter] = $this->buildConvertersConfig($value);
            }
        }

        return $parameters;
    }

    /**
     * Create an array of SortOrder objects from the specified string.
     *
     * @return SortOrder[]
     */
    private function buildSortOrders(string $orderBy): array
    {
        $result = [];
        $orders = explode(',', $orderBy);
        $orders = array_map('trim', $orders);

        foreach ($orders as $order) {
            $parts = explode(' ', $order);

            if (count($parts) > 2) {
                throw new InvalidConfigException(sprintf('The sort order "%s" is not valid.', $order));
            }

            $column = $parts[0];
            $direction = $parts[1] ?? SortOrder::DIRECTION_ASC;

            $result[] = new SortOrder($column, $direction);
        }

        return $result;
    }
}
