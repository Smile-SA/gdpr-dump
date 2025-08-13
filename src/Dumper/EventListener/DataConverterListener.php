<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\EventListener;

use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Configuration\ConfigurationBuilder;
use Smile\GdprDump\Converter\ConditionBuilder;
use Smile\GdprDump\Converter\Converter;
use Smile\GdprDump\Converter\ConverterBuilder;
use Smile\GdprDump\Converter\ConverterFactory;
use Smile\GdprDump\Dumper\DumpContext;
use Smile\GdprDump\Dumper\Event\DumpEvent;
use Smile\GdprDump\Dumper\Exception\DumpException;
use Throwable;

final class DataConverterListener
{
    private DumpContext $dumpContext;

    /**
     * @var Converter[][]
     */
    private array $converters = [];

    /**
     * @var string[]
     */
    private array $skipConditions = [];

    public function __construct(private ConverterFactory $converterFactory, private ConfigurationBuilder $configurationFactory)
    {
    }

    /**
     * Create the data conversion hook that will be applied during dump creation.
     */
    public function __invoke(DumpEvent $event): void
    {
        $this->dumpContext = $event->getDumpContext();
        $this->buildConverters($event->getConfig());
        $event->getDumper()->setTransformTableRowHook($this->getHook());
    }

    /**
     * Get the data conversion hook function.
     */
    private function getHook(): callable
    {
        return function (string $table, array $row): array {
            // Please keep in mind that this method must be as fast as possible
            // Every micro-optimization counts, this method can be executed billions of times
            // In this part of the code, abstraction layers should be avoided at all costs
            if (!isset($this->converters[$table])) {
                return $row;
            }

            // Initialize the context data
            $this->dumpContext->currentRow = $row;
            $this->dumpContext->processedData = [];

            // Evaluate the skip condition (done after context initialization as it may depend on it)
            if (isset($this->skipConditions[$table]) && eval($this->skipConditions[$table])) {
                return $row;
            }

            foreach ($this->converters[$table] as $column => $converter) {
                // Skip conversion if the column does not exist or the value is null
                if (!isset($row[$column])) {
                    continue;
                }

                // Convert the value
                try {
                    $row[$column] = $converter->convert($row[$column]);
                    $this->dumpContext->processedData[$column] = $row[$column];
                } catch (Throwable $e) {
                    // Add the table and column names to the exception message
                    throw new DumpException(sprintf('[%s.%s] %s', $table, $column, $e->getMessage()), $e);
                }
            }

            return $row;
        };
    }

    /**
     * Create the converters, grouped by table.
     */
    private function buildConverters(Configuration $configuration): void
    {
        $conditionBuilder = new ConditionBuilder();
        $converterBuilder = (new ConverterBuilder($this->converterFactory, $this->configurationFactory))
            ->setDumpContext($this->dumpContext);

        $this->converters = [];
        $this->skipConditions = [];

        foreach ($configuration->getTablesConfig() as $tableName => $tableConfig) {
            foreach ($tableConfig->getConvertersConfig() as $columnName => $converterConfig) {
                try {
                    $this->converters[$tableName][$columnName] = $converterBuilder->build($converterConfig);
                } catch (Throwable $e) {
                    // Add the table and column names to the exception message
                    throw new DumpException(sprintf('[%s.%s] %s', $tableName, $columnName, $e->getMessage()), $e);
                }
            }

            // Build conversion skip conditions
            $skipCondition = $tableConfig->getSkipCondition();
            if ($skipCondition !== '') {
                try {
                    $this->skipConditions[$tableName] = $conditionBuilder->build($skipCondition);
                } catch (Throwable $e) {
                    // Add the table name to the exception message
                    throw new DumpException(sprintf('[%s] %s', $tableName, $e->getMessage()), $e);
                }
            }
        }
    }
}
