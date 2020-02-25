<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Mysqldump;

use Ifsnop\Mysqldump\Mysqldump;
use Smile\GdprDump\Converter\ConverterFactory;
use Smile\GdprDump\Dumper\Config\DumperConfig;

class DataConverterExtension implements ExtensionInterface
{
    /**
     * @var ConverterFactory
     */
    private $converterFactory;

    /**
     * @var DumperConfig
     */
    private $config;

    /**
     * @var array
     */
    private $context = [];

    /**
     * @var array
     */
    private $converters;

    /**
     * @var array
     */
    private $skipConditions;

    /**
     * @var array
     */
    private $currentRow = [];

    /**
     * @var bool
     */
    private $skipRowConversion = false;

    /**
     * @param ConverterFactory $converterFactory
     * @param DumperConfig $config
     * @param array $context
     */
    public function __construct(DumperConfig $config, ConverterFactory $converterFactory, array $context = [])
    {
        $this->config = $config;
        $this->converterFactory = $converterFactory;
        $this->context = $context;
    }

    /**
     * @inheritdoc
     */
    public function register(Mysqldump $dumper)
    {
        if ($this->converters === null) {
            $this->prepareConverters();
        }

        $dumper->setTransformColumnValueHook($this->getHook());
    }

    /**
     * Get the data conversion hook function.
     *
     * @return callable
     * @SuppressWarnings(PHPMD.EvalExpression)
     */
    private function getHook(): callable
    {
        return function (string $table, string $column, $value, array $row) {
            // Please keep in mind that this method must be as fast as possible
            // Every micro-optimization counts, this method can be executed billions of times
            // In this part of the code, abstraction layers should be avoided at all costs

            if (!isset($this->converters[$table][$column]) || $value === null) {
                return $value;
            }

            if ($this->currentRow !== $row) {
                $this->currentRow = $row;

                // Set the context data
                $this->context['row_data'] = $row;
                $this->context['processed_data'] = [];

                // Evaluate the skip condition
                if (isset($this->skipConditions[$table])) {
                    $this->skipRowConversion = eval($this->skipConditions[$table]);
                }
            }

            if ($this->skipRowConversion) {
                return $value;
            }

            // Transform the value
            $value = $this->converters[$table][$column]->convert($value, $this->context);
            $this->context['processed_data'][$column] = $value;

            return $value;
        };
    }

    /**
     * Create the converters, grouped by table.
     */
    private function prepareConverters()
    {
        $this->converters = [];
        $this->skipConditions = [];

        foreach ($this->config->getTablesConfig() as $tableName => $tableConfig) {
            foreach ($tableConfig->getConverters() as $columnName => $definition) {
                $this->converters[$tableName][$columnName] = $this->converterFactory->create($definition);
            }

            $skipCondition = $tableConfig->getSkipCondition();
            if ($skipCondition !== '') {
                $this->skipConditions[$tableName] = $skipCondition;
            }
        }
    }
}
