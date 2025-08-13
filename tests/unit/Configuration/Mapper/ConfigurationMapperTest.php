<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Mapper;

use PDO;
use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Configuration\Definition\ConverterConfig;
use Smile\GdprDump\Configuration\Definition\Table\Direction;
use Smile\GdprDump\Configuration\Exception\UnexpectedValueException;
use Smile\GdprDump\Configuration\Mapper\ConfigurationMapper;
use Smile\GdprDump\Configuration\Mapper\SortOrderMapper;
use Smile\GdprDump\Tests\Unit\TestCase;

final class ConfigurationMapperTest extends TestCase
{
    /**
     * Test mapping of the strict_schema property.
     */
    public function testMapStrictMode(): void
    {
        $data = ['strict_schema' => true];

        $this->assertTrue($this->createConfig($data)->isStrictSchema());
    }

    /**
     * Test creation of the connections params.
     */
    public function testMapDatabase(): void
    {
        $data = [
            'database' => [
                'host' => 'myhost',
                'name' => 'mydb',
                'not_exists' => true, // not allowed but there is not validation of the connection params currently
                'driver_options' => [
                    PDO::MYSQL_ATTR_SSL_CERT => 'cert',
                ],
            ],
        ];

        $connectionParams = $this->createConfig($data)->getConnectionParams();

        // name is renamed to "dbname", driver_option is renamed to "driverOptions"
        $this->assertEqualsCanonicalizing(
            ['host', 'dbname', 'not_exists', 'driverOptions'],
            array_keys($connectionParams)
        );
        $this->assertSame($data['database']['host'], $connectionParams['host']);
        $this->assertSame($data['database']['name'], $connectionParams['dbname']);
        $this->assertSame($data['database']['driver_options'], $connectionParams['driverOptions']);
    }

    /**
     * Test creation of the DumpConfig object.
     */
    public function testMapDumpSettings(): void
    {
        $data = [
            'dump' => [
                'hex_blob' => true,
                'init_commands' => ['SET 1'],
            ],
        ];

        $dumpSettings = $configuration = $this->createConfig($data)->getDumpSettings();
        $this->assertSame($data['dump']['hex_blob'], $dumpSettings->getHexBlob());
        $this->assertSame($data['dump']['init_commands'], $dumpSettings->getInitCommands());
    }

    /**
     * Test creation of the FakerConfig object.
     */
    public function testMapFakerSettings(): void
    {
        $data = [
            'faker' => [
                'locale' => 'fr_FR',
            ],
        ];

        $fakerConfig = $this->createConfig($data)->getFakerConfig();
        $this->assertSame($data['faker']['locale'], $fakerConfig->getLocale());
    }

    /**
     * Test creation of the FilterPropagationConfig object.
     */
    public function testMapFilterPropagationConfig(): void
    {
        $data = [
            'filter_propagation' => [
                'enabled' => false,
                'ignored_foreign_keys' => ['FK1', 'FK2'],
            ],
        ];

        $propagationConfig = $this->createConfig($data)->getFilterPropagationConfig();
        $this->assertSame($data['filter_propagation']['enabled'], $propagationConfig->isEnabled());
        $this->assertSame(
            $data['filter_propagation']['ignored_foreign_keys'],
            $propagationConfig->getIgnoredForeignKeys()
        );
    }

    /**
     * Test creation of included/excluded tables.
     */
    public function testMapIncludedAndExcludedTables(): void
    {
        $data = [
            'tables_whitelist' => ['included1', 'included2'],
            'tables_blacklist' => ['excluded1', 'excluded2'],
        ];

        $configuration = $this->createConfig($data);
        $this->assertSame($data['tables_whitelist'], $configuration->getIncludedTables());
        $this->assertSame($data['tables_blacklist'], $configuration->getExcludedTables());
    }

    /**
     * Test mapping of the variables property.
     */
    public function testMapVariables(): void
    {
        $data = ['variables' => ['var1' => 'SELECT 1', 'var2' => 'SELECT 2']];

        $this->assertSame($data['variables'], $this->createConfig($data)->getSqlVariables());
    }

    /**
     * Test creation of the TableConfigMap object (filters).
     */
    public function testMapTableFilters(): void
    {
        $data = [
            'tables' => [
                'log_*' => [
                    'truncate' => true,
                ],
                'products' => [
                    'order_by' => 'name desc',
                    'where' => '1=1',
                    'limit' => 10000,
                ],
            ],
        ];

        $tableConfigs = $this->createConfig($data)->getTableConfigs();
        $this->assertCount(2, $tableConfigs);

        $tableConfig = $tableConfigs->get('log_*');
        $this->assertNotNull($tableConfig);
        $this->assertSame($data['tables']['log_*']['truncate'], $tableConfig->isTruncate());

        $tableConfig = $tableConfigs->get('products');
        $this->assertNotNull($tableConfig);

        $this->assertSame($data['tables']['products']['where'], $tableConfig->getWhere());
        $this->assertSame($data['tables']['products']['limit'], $tableConfig->getLimit());

        // order_by parameter must be converted to an array of SortOrder objects
        $sortOrders = $tableConfig->getSortOrders();
        $this->assertCount(1, $sortOrders);
        $this->assertArrayHasKey(0, $sortOrders);

        $this->assertSame('name', $sortOrders[0]->getColumn());
        $this->assertSame(Direction::DESC, $sortOrders[0]->getDirection());
    }

    /**
     * Test creation of the TableConfigMap object (converters).
     */
    public function testMapTableConverters(): void
    {
        $data = [
            'tables' => [
                'users' => [
                    'skip_conversion_if' => '{{username}} === "admin123"',
                    'converters' => [
                        'email' => [
                            'converter' => 'randomizeEmail',
                            'parameters' => ['domains' => ['acme.com']],
                            'unique' => true,
                            'cache_key' => 'user_email',
                            'condition' => '{{username}} !== "test_user"',
                        ],
                        'username' => [
                            'converter' => 'randomizeText',
                            'disabled' => true,
                        ],
                    ],
                ],
            ],
        ];

        $tableConfigs = $this->createConfig($data)->getTableConfigs();
        $this->assertCount(1, $tableConfigs);

        $tableConfig = $tableConfigs->get('users');
        $this->assertNotNull($tableConfig);
        $this->assertSame($data['tables']['users']['skip_conversion_if'], $tableConfig->getSkipCondition());

        $converterConfigs = $tableConfig->getConverterConfigs();
        $this->assertCount(2, $converterConfigs);

        $converterConfig = $converterConfigs->get('email');
        $this->assertNotNull($converterConfig);
        $this->assertSame($data['tables']['users']['converters']['email']['converter'], $converterConfig->getName());
        $this->assertSame($data['tables']['users']['converters']['email']['unique'], $converterConfig->isUnique());
        $this->assertSame(
            $data['tables']['users']['converters']['email']['cache_key'],
            $converterConfig->getCacheKey()
        );
        $this->assertSame(
            $data['tables']['users']['converters']['email']['condition'],
            $converterConfig->getCondition()
        );
        $this->assertSame(
            $data['tables']['users']['converters']['email']['parameters'],
            $converterConfig->getParameters()
        );


        $converterConfig = $converterConfigs->get('username');
        $this->assertNotNull($converterConfig);
        $this->assertSame('noop', $converterConfig->getName()); // disabled converter => converted to "noop"
    }

    /**
     * Assert that the parameter `converters` is mapped to an array of ConverterConfig objects.
     */
    public function testMapConvertersParameter(): void
    {
        $data = [
            'tables' => [
                'users' => [
                    'converters' => [
                        'json_data' => [
                            'converter' => 'jsonData',
                            'parameters' => [
                                'converters' => [
                                    'firstname' => ['converter' => 'randomizeText', 'cache_key' => 'key1'],
                                    'lastname' => ['converter' => 'randomizeText'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $tableConfig = $this->createConfig($data)->getTableConfigs()->get('users');
        $this->assertNotNull($tableConfig);

        $converterConfig = $tableConfig->getConverterConfigs()->get('json_data');
        $this->assertNotNull($converterConfig);

        $parameters = $converterConfig->getParameters();
        $this->assertNotEmpty($parameters);
        $this->assertArrayHasKey('converters', $parameters);
        $this->assertIsArray($parameters['converters']);
        $this->assertCount(2, $parameters['converters']);

        $this->assertArrayHasKey('firstname', $parameters['converters']);
        $this->assertIsObject($parameters['converters']['firstname']);
        $this->assertInstanceOf(ConverterConfig::class, $parameters['converters']['firstname']);
        $this->assertSame(
            $data['tables']['users']['converters']['json_data']['parameters']['converters']['firstname']['converter'],
            $parameters['converters']['firstname']->getName()
        );
        $this->assertSame(
            $data['tables']['users']['converters']['json_data']['parameters']['converters']['firstname']['cache_key'],
            $parameters['converters']['firstname']->getCacheKey()
        );

        $this->assertArrayHasKey('lastname', $parameters['converters']);
        $this->assertIsObject($parameters['converters']['lastname']);
        $this->assertInstanceOf(ConverterConfig::class, $parameters['converters']['lastname']);
        $this->assertSame(
            $data['tables']['users']['converters']['json_data']['parameters']['converters']['lastname']['converter'],
            $parameters['converters']['lastname']->getName()
        );
    }

    /**
     * Assert that the parameter `converter` is mapped to a ConverterConfig object.
     */
    public function testMapConverterParameter(): void
    {
        $data = [
            'tables' => [
                'foo' => [
                    'converters' => [
                        'bar' => [
                            'converter' => 'mock',
                            'parameters' => [
                                'converter' => ['converter' => 'randomizeEmail', 'unique' => true],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $tableConfig = $this->createConfig($data)->getTableConfigs()->get('foo');
        $this->assertNotNull($tableConfig);

        $converterConfig = $tableConfig->getConverterConfigs()->get('bar');
        $this->assertNotNull($converterConfig);

        $parameters = $converterConfig->getParameters();
        $this->assertNotEmpty($parameters);
        $this->assertArrayHasKey('converter', $parameters);

        $this->assertIsObject($parameters['converter']);
        $this->assertInstanceOf(ConverterConfig::class, $parameters['converter']);
        $this->assertTrue($parameters['converter']->isUnique());
    }

    /**
     * Assert that an exception is thrown when using the deprecated parameter "filters".
     */
    public function testDeprecatedFilters(): void
    {
        $data = [
            'tables' => [
                'users' => [
                    'filters' => [],
                ],
            ],
        ];

        $this->expectException(UnexpectedValueException::class);
        $this->createConfig($data);
    }

    /**
     * Assert that an exception is thrown when mapping an invalid dump parameter.
     */
    public function testInvalidDumpParameter(): void
    {
        $data = ['dump' => ['not_exists' => true]];
        $this->expectException(UnexpectedValueException::class);
        $this->createConfig($data);
    }

    /**
     * Assert that an exception is thrown when mapping an invalid faker parameter.
     */
    public function testInvalidFakerParameter(): void
    {
        $data = ['faker' => ['not_exists' => true]];
        $this->expectException(UnexpectedValueException::class);
        $this->createConfig($data);
    }

    /**
     * Assert that an exception is thrown when mapping an invalid filter propagation parameter.
     */
    public function testInvalidFilterPropagationParameter(): void
    {
        $data = ['filter_propagation' => ['not_exists' => true]];
        $this->expectException(UnexpectedValueException::class);
        $this->createConfig($data);
    }

    /**
     * Assert that an exception is thrown when mapping an invalid table parameter.
     */
    public function testInvalidTableParameter(): void
    {
        $data = ['tables' => ['users' => ['not_exists' => true]]];
        $this->expectException(UnexpectedValueException::class);
        $this->createConfig($data);
    }

    /**
     * Assert that an exception is thrown when a converter name is missing.
     */
    public function testMissingConverterName(): void
    {
        $data = [
            'tables' => [
                'users' => [
                    'converters' => [
                        'username' => ['unique' => true],
                    ],
                ],
            ],
        ];

        $this->expectException(UnexpectedValueException::class);
        $this->createConfig($data);
    }

    /**
     * Assert that an exception is thrown when mapping an invalid converter parameter.
     */
    public function testInvalidConverterParameter(): void
    {
        $data = [
            'tables' => [
                'users' => [
                    'converters' => [
                        'username' => ['not_exists' => true],
                    ],
                ],
            ],
        ];

        $this->expectException(UnexpectedValueException::class);
        $this->createConfig($data);
    }

    /**
     * Create a configuration object from the provided data.
     */
    private function createConfig(array $data): Configuration
    {
        $mapper = new ConfigurationMapper(new SortOrderMapper());

        return $mapper->fromArray($data);
    }
}
