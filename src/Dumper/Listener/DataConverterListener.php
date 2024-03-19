<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Listener;

use Exception;
use RuntimeException;
use Smile\GdprDump\Converter\ConditionBuilder;
use Smile\GdprDump\Converter\ConverterBuilder;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Dumper\Config\DumperConfig;
use Smile\GdprDump\Dumper\Event\DumpEvent;

class DataConverterListener
{
    private array $context = [];

    /**
     * @var ConverterInterface[][]
     */
    private array $converters = [];

    /**
     * @var string[]
     */
    private array $skipConditions = [];

    public function __construct(
        private ConverterBuilder $converterBuilder,
        private ConditionBuilder $conditionBuilder
    ) {
    }

    /**
     * Create the data conversion hook that will be applied during dump creation.
     */
    public function __invoke(DumpEvent $event): void
    {
        $this->buildConverters($event->getConfig());

        $this->context = $event->getContext();
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
            $context = $this->context;
            $context['row_data'] = $row;
            $context['processed_data'] = [];

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
                    $row[$column] = $converter->convert($row[$column], $context);
                    $context['processed_data'][$column] = $row[$column];
                } catch (Exception $e) {
                    throw new RuntimeException(sprintf('[%s.%s] %s', $table, $column, $e->getMessage()), 0, $e);
                }
            }

            return $row;
        };
    }

    /**
     * Create the converters, grouped by table.
     */
    private function buildConverters(DumperConfig $config): void
    {
        $this->converters = [];
        $this->skipConditions = [];

        foreach ($config->getTablesConfig() as $tableName => $tableConfig) {
            // Build data converters
            foreach ($tableConfig->getConverters() as $columnName => $converterDefinition) {
                $this->converters[$tableName][$columnName] = $this->converterBuilder->build($converterDefinition);
            }

            // Build conversion skip conditions
            $skipCondition = $tableConfig->getSkipCondition();
            if ($skipCondition !== '') {
                $this->skipConditions[$tableName] = $this->conditionBuilder->build($skipCondition);
            }
        }
    }
}
