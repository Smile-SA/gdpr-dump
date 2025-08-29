<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Builder;

use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Converter\Condition\Condition;
use Smile\GdprDump\Converter\Condition\ConditionBuilder;
use Smile\GdprDump\Converter\Converter;
use Smile\GdprDump\Converter\ConverterBuilder;
use Smile\GdprDump\Converter\ConverterFactory;
use Smile\GdprDump\Dumper\DumpContext;
use Smile\GdprDump\Dumper\Exception\DumpException;
use Smile\GdprDump\Faker\LazyGeneratorFactory;
use Throwable;

final class MysqldumpHookBuilder
{
    public function __construct(
        private ConverterFactory $converterFactory,
        private LazyGeneratorFactory $lazyGeneratorFactory,
    ) {
    }

    /**
     * Create the data conversion hook that will be applied during dump creation.
     */
    public function build(Configuration $configuration, DumpContext $dumpContext): callable
    {
        $converters = $this->buildConverters($configuration, $dumpContext);
        $skipConditions = $this->buildSkipConditions($configuration, $dumpContext);

        /*
         * Please keep in mind that this function must be as fast as possible,
         * because it is called billions of times when dumping huge databases.
         * Abstraction layers must be avoided at all costs.
         */
        return function (string $table, array $row) use ($dumpContext, $converters, $skipConditions): array {
            if (!isset($converters[$table])) {
                return $row;
            }

            // Initialize the context data
            $dumpContext->currentRow = $row;
            $dumpContext->processedData = [];

            // Evaluate the skip condition (done after context initialization as it may depend on it)
            if (isset($skipConditions[$table]) && $skipConditions[$table]->evaluate()) {
                return $row;
            }

            foreach ($converters[$table] as $column => $converter) {
                // Skip conversion if the column does not exist or the value is null
                if (!isset($row[$column])) {
                    continue;
                }

                // Convert the value
                try {
                    $row[$column] = $converter->convert($row[$column]);
                    $dumpContext->processedData[$column] = $row[$column];
                } catch (Throwable $e) {
                    // Add the table and column names to the exception message
                    throw new DumpException(sprintf('[%s.%s] %s', $table, $column, $e->getMessage()), $e);
                }
            }

            return $row;
        };
    }

    /**
     * Create the converters, indexed by table name.
     *
     * @return array<string, array<string, Converter>>
     */
    private function buildConverters(Configuration $configuration, DumpContext $dumpContext): array
    {
        $converters = [];
        $converterBuilder = new ConverterBuilder(
            $this->converterFactory,
            $dumpContext,
            $this->lazyGeneratorFactory->create($configuration->getFakerConfig()->getLocale())
        );

        foreach ($configuration->getTableConfigs() as $tableName => $tableConfig) {
            foreach ($tableConfig->getConverterConfigs() as $columnName => $converterConfig) {
                try {
                    $converters[$tableName][$columnName] = $converterBuilder->build($converterConfig);
                } catch (Throwable $e) {
                    // Add the table and column names to the exception message
                    throw new DumpException(sprintf('[%s.%s] %s', $tableName, $columnName, $e->getMessage()), $e);
                }
            }
        }

        return $converters;
    }

    /**
     * Create the skip conditions, indexed by table name.
     *
     * @return array<string, Condition>
     */
    private function buildSkipConditions(Configuration $configuration, DumpContext $dumpContext): array
    {
        $skipConditions = [];
        $conditionBuilder = new ConditionBuilder($dumpContext);

        foreach ($configuration->getTableConfigs() as $tableName => $tableConfig) {
            $skipCondition = $tableConfig->getSkipCondition();
            if ($skipCondition !== '') {
                try {
                    $skipConditions[$tableName] = $conditionBuilder->build($skipCondition);
                } catch (Throwable $e) {
                    // Add the table name to the exception message
                    throw new DumpException(sprintf('[%s] %s', $tableName, $e->getMessage()), $e);
                }
            }
        }

        return $skipConditions;
    }
}
