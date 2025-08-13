<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Mapper;

use Smile\GdprDump\Config\Definition\ConverterConfig;
use Smile\GdprDump\Config\Definition\DumpConfig;
use Smile\GdprDump\Config\Definition\FakerConfig;
use Smile\GdprDump\Config\Definition\FilterPropagationConfig;
use Smile\GdprDump\Config\Definition\Table\SortOrder;
use Smile\GdprDump\Config\Definition\TableConfig;
use Smile\GdprDump\Config\DumperConfig;
use Smile\GdprDump\Config\Exception\InvalidJsonSchemaException;
use Smile\GdprDump\Config\Exception\JsonSchemaValidationException;
use Smile\GdprDump\Config\Exception\MappingException;
use Smile\GdprDump\Config\Validator\SchemaValidator;
use stdClass;

// TODO use in config loader
final class ConfigMapper implements ObjectMapper
{
    public function __construct(private SchemaValidator $validator)
    {
    }

    /**
     * Create a DumperConfig object from the specified domain object.
     *
     * @throws SchemaValidationException if the provided object contains invalid data
     * @throws MappingException if an error occurred while mapping the object data to the config
     */
    public function map(object $from, object $to): void
    {
        if (!$from instanceof stdClass) {
            throw new MappingException(
                sprintf('Invalid source type. Expected "%s", got "%s".', stdClass::class, get_class($from))
            );
        }

        if (!$to instanceof DumperConfig) {
            throw new MappingException(
                sprintf('Invalid destination type. Expected "%s", got "%s".', DumperConfig::class, get_class($to))
            );
        }

        $this->validateObject($from);

        foreach (get_object_vars($from) as $property => $value) {
            match ($property) {
                'database' => $to->setConnectionParams($value),
                'dump' => $to->setDumpSettings($value),
                'faker' => $to->setFakerConfig($this->makeFakerConfig($value)),
                'filter_propagation' => $to->setFilterPropagationConfig($this->makeFilterPropagationConfig($value)),
                'tables_blacklist' => $to->setExcludedTables($value),
                'tables_whitelist' => $to->setIncludedTables($value),
                'tables' => $to->setTablesConfig(
                    array_map(fn (stdClass $table) => $this->makeTableConfig($table), $value)
                ),
                'strict_schema' => $to->setStrictSchema($value),
                'variables' => $to->setVarQueries(get_object_vars($value)),
                'requires_version', 'version', 'if_version' => null, // not mapped
                default => throw new MappingException(sprintf('Unsupported config property "%s".', $property)),
            };
        }
    }

    private function makeDumpConfig(object $object): DumpConfig
    {
        $config = new DumpConfig();

        foreach (get_object_vars($object) as $property => $value) {
            $items[$property] = match ($property) {
                'add_drop_database' => $config->setAddDropDatabase($value),
                'add_drop_table' => $config->setAddDropTable($value),
                'add_drop_trigger' => $config->setAddDropTrigger($value),
                'add_locks' => $config->setAddLocks($value),
                'complete_insert' => $config->setCompleteInsert($value),
                'compress' => $config->setCompress($value),
                'default_character_set' => $config->setDefaultCharacterSet($value),
                'disable_keys' => $config->setDisableKeys($value),
                'events' => $config->setEvents($value),
                'extended_insert' => $config->setExtendedInsert($value),
                'hex_blob' => $config->setHexBlob($value),
                'init_commands' => $config->setInitCommands($value),
                'insert_ignore' => $config->setInsertIgnore($value),
                'lock_tables' => $config->setLockTables($value),
                'net_buffer_length' => $config->setNetBufferLength($value),
                'no_autocommit' => $config->setNoAutocommit($value),
                'no_create_info' => $config->setNoCreateInfo($value),
                'output' => $config->setOutput($value),
                'routines' => $config->setRoutines($value),
                'single_transaction' => $config->setSingleTransaction($value),
                'skip_comments' => $config->setSkipComments($value),
                'skip_definer' => $config->setSkipDefiner($value),
                'skip_dump_date' => $config->setSkipDumpDate($value),
                'skip_triggers' => $config->setSkipTriggers($value),
                'skip_tz_utc' => $config->setSkipTzUtc($value),
                default => throw new MappingException(sprintf('Unsupported dump property "%s".', $property)),
            };
        }

        return $config;
    }

    /**
     * Create a FakerConfig object from the specified domain object.
     */
    private function makeFakerConfig(object $object): FakerConfig
    {
        $config = new FakerConfig();

        foreach (get_object_vars($object) as $property => $value) {
            match ($property) {
                'locale' => $config->setLocale($value),
                default => throw new MappingException(sprintf('Unsupported faker property "%s".', $property)),
            };
        }

        return $config;
    }

    /**
     * Create a FilterPropagation object from the specified domain object.
     */
    private function makeFilterPropagationConfig(object $object): FilterPropagationConfig
    {
        $config = new FilterPropagationConfig();

        foreach (get_object_vars($object) as $property => $value) {
            match ($property) {
                'enabled' => $config->setEnabled($value),
                'ignored_foreign_keys' => $config->setIgnoredForeignKeys($value),
                default => throw new MappingException(sprintf('Unsupported propagation property "%s".', $property)),
            };
        }

        return $config;
    }

    /**
     * Create a TableConfig object from the specified domain object.
     */
    private function makeTableConfig(object $object)
    {
        $config = new TableConfig();

        foreach (get_object_vars($object) as $property => $value) {
            match ($property) {
                'truncate' => $config->setTruncate($value),
                'where' => $config->setWhere($value),
                'limit' => $config->setLimit($value),
                'order_by' => $config->setSortOrders($this->buildSortOrders($value)),
                'skip_conversion_if' => $config->setSkipCondition($value),
                // Remove disabled converters
                'converters' => $config->setConvertersConfig(
                    array_map(
                        fn (stdClass $converter) => $this->makeConverterConfig($converter),
                        array_filter(
                            $value,
                            fn (stdClass $converter) => !property_exists($converter, 'disabled') || !$converter->disabled
                        )
                    )
                ),
                'filters' => throw new MappingException('The table property "filters" is no longer supported, use "where" instead.'),
                default => throw new MappingException(sprintf('Unsupported table property "%s".', $property)),
            };
        }

        return $config;
    }

    /**
     * Create a ConverterConfig object from the specified domain object.
     */
    private function makeConverterConfig(object $object): ConverterConfig
    {
        $config = new ConverterConfig($object->converter);

        foreach (get_object_vars($object) as $property => $value) {
            match ($property) {
                'parameters' => $config->setParameters($value),
                'condition' => $config->setCondition($value),
                'cache_key' => $config->setCacheKey($value),
                'unique' => $config->setUnique($value),
                'converter' => null, // already set in constructor
                default => throw new MappingException(sprintf('Unsupported converter property "%s".', $property)),
            };
        }

        return $config;
    }

    /**
     * Validate the object to convert.
     *
     * @throws SchemaValidationException
     * @throws MappingException
     */
    // TODO use
    private function validateObject(stdClass $object): void
    {
        try {
            $result = $this->validator->validate($object);
            if (!$result->isValid()) {
                throw new JsonSchemaValidationException($result->getMessages());
            }
        } catch (InvalidJsonSchemaException $e) {
            throw new MappingException($e->getMessage(), $e);
        }
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
                throw new MappingException(sprintf('The sort order "%s" is not valid.', $order));
            }

            $column = $parts[0];
            $direction = $parts[1] ?? SortOrder::DIRECTION_ASC;

            $result[] = new SortOrder($column, $direction);
        }

        return $result;
    }
}
