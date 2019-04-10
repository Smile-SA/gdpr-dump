<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Dumper;

use Ifsnop\Mysqldump\Mysqldump;
use Smile\Anonymizer\Config\ConfigInterface;
use Smile\Anonymizer\Converter\ConverterFactory;
use Smile\Anonymizer\Dumper\Sql\ColumnTransformer;
use Smile\Anonymizer\Dumper\Sql\Driver\DriverFactory;
use Smile\Anonymizer\Dumper\Sql\DumperConfig;

class SqlDumper implements DumperInterface
{
    /**
     * @var ConverterFactory
     */
    private $converterFactory;

    /**
     * @param ConverterFactory $converterFactory
     */
    public function __construct(ConverterFactory $converterFactory)
    {
        $this->converterFactory = $converterFactory;
    }

    /**
     * @@inheritdoc
     */
    public function dump(ConfigInterface $config)
    {
        // Use a config wrapper with getters/setters
        $config = new DumperConfig($config);

        // Create the dump
        $dumper = $this->getDumperInstance($config);
        $dumper->start($config->getDumpFile());

        return $dumper;
    }

    /**
     * Get the MySQL dumper.
     *
     * @param DumperConfig $config
     * @return Mysqldump
     * @throws \Exception
     */
    private function getDumperInstance(DumperConfig $config): Mysqldump
    {
        $database = $config->getDatabase();
        $driver = DriverFactory::create($config->getDatabase()->getDriver());

        $dumper = new Mysqldump(
            $driver->getDsn($database),
            $database->getUser(),
            $database->getPassword(),
            $config->getDumpSettings(),
            $database->getPdoSettings()
        );

        // Set the column transformer
        $converters = $this->getConverters($config);
        $columnTransformer = new ColumnTransformer($converters);
        $dumper->setTransformColumnValueHook([$columnTransformer, 'transform']);

        return $dumper;
    }

    /**
     * Get the converters from the config.
     *
     * @param DumperConfig $config
     * @return array
     */
    private function getConverters(DumperConfig $config): array
    {
        $converters = [];

        foreach ($config->getTablesConfig() as $tableName => $tableConfig) {
            foreach ($tableConfig->getConverters() as $columnName => $definition) {
                $converters[$tableName][$columnName] = $this->converterFactory->create($definition);
            }
        }

        return $converters;
    }
}
