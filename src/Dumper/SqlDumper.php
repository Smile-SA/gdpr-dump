<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Dumper;

use Ifsnop\Mysqldump\Mysqldump;
use Smile\Anonymizer\Config\ConfigInterface;
use Smile\Anonymizer\Converter\ConverterFactory;
use Smile\Anonymizer\Dumper\Sql\ColumnTransformer;
use Smile\Anonymizer\Dumper\Sql\Config\DatabaseConfig;
use Smile\Anonymizer\Dumper\Sql\Doctrine\ConnectionFactory;
use Smile\Anonymizer\Dumper\Sql\Driver\DriverFactory;
use Smile\Anonymizer\Dumper\Sql\DumperConfig;
use Smile\Anonymizer\Dumper\Sql\TableDependency\FilterBuilder;
use Smile\Anonymizer\Dumper\Sql\TableFinder;

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
    public function dump(ConfigInterface $config): DumperInterface
    {
        // Get the database config
        $databaseConfig = new DatabaseConfig($config);

        // Create a doctrine connection
        $connection = ConnectionFactory::create($databaseConfig);

        // Use a config wrapper with getters/setters
        $tableFinder = new TableFinder($connection);
        $config = new DumperConfig($config, $tableFinder);

        // Create the dumper instance
        $dumper = $this->getDumperInstance($config);

        // Set the table filters
        $filterBuilder = new FilterBuilder($config, $connection);
        $tableWheres = $filterBuilder->getTableFilters();
        $dumper->setTableWheres($tableWheres);

        // Close the doctrine connection
        $connection->close();

        $dumper->start($config->getDumpOutput());

        return $this;
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
        $driver = DriverFactory::create($database->getDriver());

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
