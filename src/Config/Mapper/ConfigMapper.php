<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Mapper;

use Smile\GdprDump\Config\ConverterConfig;
use Smile\GdprDump\Config\DumperConfig;
use Smile\GdprDump\Config\Exception\InvalidJsonSchemaException;
use Smile\GdprDump\Config\Exception\MappingException;
use Smile\GdprDump\Config\Exception\SchemaValidationException;
use Smile\GdprDump\Config\FakerConfig;
use Smile\GdprDump\Config\FilterPropagationConfig;
use Smile\GdprDump\Config\Loader\ContainerInterface;
use Smile\GdprDump\Config\TableConfig;
use Smile\GdprDump\Config\Validator\SchemaValidator;
use Smile\GdprDump\Dumper\Config\Definition\Table\SortOrder;
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

        foreach ($this->objectToArray($from) as $property => $value) {
            match ($property) {
                'database' => $to->setConnectionParams($value),
                'dump' => $to->setDumpSettings($value),
                'faker' => $to->setFakerConfig($this->makeFakerConfig($value)),
                'filter_propagation' => $to->setFilterPropagationConfig($this->makeFilterPropagationConfig($value)),
                'tables_blacklist' => $to->setExcludedTables($value),
                'tables_whitelist' => $to->setIncludedTables($value),
                'tables' => $to->setTablesConfig(
                    array_map(fn (array $table) => $this->makeTableConfig($table), $value)
                ),
                'strict_schema' => $to->setStrictSchema($value),
                'variables' => $to->setVariables($value),
                'requires_version', 'version', 'if_version' => null, // not mapped
                default => throw new MappingException(sprintf('Unsupported config property "%s".', $property)),
            };
        }
    }

    /**
     * Create a FakerConfig object from the specified domain object.
     */
    private function makeFakerConfig(array $data)
    {
        $config = new FakerConfig();

        foreach ($data as $property => $value) {
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
    private function makeFilterPropagationConfig(array $data)
    {
        $config = new FilterPropagationConfig();

        foreach ($data as $property => $value) {
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
    private function makeTableConfig(array $data)
    {
        $config = new TableConfig();

        foreach ($data as $property => $value) {
            match ($property) {
                'truncate' => $config->setTruncate($value),
                'where' => $config->setWhere($value),
                'limit' => $config->setLimit($value),
                'order_by' => $config->setSortOrders($this->buildSortOrders($value)),
                'skip_conversion_if' => $config->setSkipCondition($value),
                // Remove disabled converters
                'converters' => $config->setConvertersConfig(
                    array_map(
                        fn (array $converter) => $this->makeConverterConfig($converter),
                        array_filter(
                            $value,
                            fn (array $converter) => !array_key_exists('disabled', $converter) || !$converter['disabled'],
                        ),
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
    private function makeConverterConfig(array $data)
    {
        $config = new ConverterConfig($data['converter']);

        foreach ($data as $property => $value) {
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
    private function validateObject(ContainerInterface $container): void
    {
        try {
            $result = $this->validator->validate($container);
            if (!$result->isValid()) {
                throw new InvalidJsonSchemaException($result->getMessages());
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

    /**
     * Recursively convert all objects found to arrays.
     */
    private function objectToArray(array|object $data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        foreach ($data as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $data[$key] = $this->objectToArray($value);
            }
        }

        return $data;
    }
}
