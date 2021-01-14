<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Mysqldump;

use Smile\GdprDump\Converter\ConverterFactory;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Dumper\Config\DumperConfig;
use Smile\GdprDump\Faker\FakerService;

class DataConverterExtension implements ExtensionInterface
{
    /**
     * @var ConverterFactory
     */
    private $converterFactory;

    /**
     * @var FakerService
     */
    private $faker;

    /**
     * @var array
     */
    private $context;

    /**
     * @var ConverterInterface[][]
     */
    private $converters;

    /**
     * @var string[]
     */
    private $skipConditions;

    /**
     * @param ConverterFactory $converterFactory
     * @param FakerService $faker
     */
    public function __construct(ConverterFactory $converterFactory, FakerService $faker)
    {
        $this->converterFactory = $converterFactory;
        $this->faker = $faker;
    }

    /**
     * @inheritdoc
     */
    public function register(Context $context): void
    {
        $this->context = $context->getDumperContext();

        // Set the Faker locale
        $locale = (string) ($context->getConfig()->getFakerSettings()['locale'] ?? '');
        if ($locale !== '') {
            $this->faker->setLocale($locale);
        }

        // Prepare converters
        if ($this->converters === null) {
            $this->prepareConverters($context->getConfig());
        }

        $context->getDumper()->setTransformTableRowHook($this->getHook());
    }

    /**
     * Get the data conversion hook function.
     *
     * @return callable
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
                $row[$column] = $converter->convert($row[$column], $context);
                $context['processed_data'][$column] = $row[$column];
            }

            return $row;
        };
    }

    /**
     * Create the converters, grouped by table.
     *
     * @param DumperConfig $config
     */
    private function prepareConverters(DumperConfig $config): void
    {
        $this->converters = [];
        $this->skipConditions = [];

        foreach ($config->getTablesConfig() as $tableName => $tableConfig) {
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
