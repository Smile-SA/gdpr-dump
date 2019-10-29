<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional\Config;

use PDO;
use Smile\GdprDump\Config\Validator\JsonSchemaValidator;
use Smile\GdprDump\Tests\Functional\TestCase;
use stdClass;

class JsonSchemaValidatorTest extends TestCase
{
    /**
     * @var JsonSchemaValidator
     */
    private $validator;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $schemaFile = $this->getBasePath() . '/app/config/schema.json';
        $this->validator = new JsonSchemaValidator($schemaFile);
    }

    /**
     * Test the database settings.
     */
    public function testDatabaseSettings()
    {
        $data = [
            'database' => [
                'name' => 'mydatabase',
                'user' => 'myuser',
                'password' => 'mypassword',
                'host' => 'myhost',
                'port' => '3306',
                'driver' => 'pdo_mysql',
                'charset' => 'utf8mb',
                'driver_options' => [
                    PDO::MYSQL_ATTR_LOCAL_INFILE => true,
                ],
            ],
        ];

        $this->assertDataIsValid($data);

        // Check if the validation fails when an invalid driver is used
        $data = ['database' => ['driver' => 'not_exists']];
        $this->assertDataIsNotValid($data);

        // Check if the validation fails when an unknown parameter is used
        $data = ['database' => ['not_exists' => true]];
        $this->assertDataIsNotValid($data);

        // Check if the validation fails when a parameter has the wrong type
        $data = ['database' => ['charset' => 1.5]];
        $this->assertDataIsNotValid($data);
    }

    /**
     * Test the dump settings.
     */
    public function testDumpSettings()
    {
        $data = [
            'dump' => [
                'output' => 'my_dump_file-{Y-m-d H:i:s}.sql.gz',
                'compress' => 'gzip',
                'init_commands' => [],
                'add_drop_database' => false,
                'add_drop_table' => true,
                'add_drop_trigger' => true,
                'add_locks' => true,
                'complete_insert' => false,
                'default_character_set' => 'utf8',
                'disable_keys' => true,
                'extended_insert' => true,
                'events' => false,
                'hex_blob' => false,
                'insert_ignore' => false,
                'net_buffer_length' => 1000000,
                'no_autocommit' => true,
                'no_create_info' => false,
                'lock_tables' => false,
                'routines' => false,
                'single_transaction' => true,
                'skip_triggers' => false,
                'skip_tz_utc' => false,
                'skip_comments' => false,
                'skip_dump_date' => false,
                'skip_definer' => false,
            ],
        ];

        $this->assertDataIsValid($data);

        // Check if the validation fails when an invalid driver is used
        $data = ['dump' => ['compress' => 'not_exists']];
        $this->assertDataIsNotValid($data);

        // Check if the validation fails when an unknown parameter is used
        $data = ['dump' => ['not_exists' => true]];
        $this->assertDataIsNotValid($data);

        // Check if the validation fails when a parameter has the wrong type
        $data = ['dump' => ['output' => 1.5]];
        $this->assertDataIsNotValid($data);
    }

    /**
     * Test the tables whitelist.
     */
    public function testTablesWhitelist()
    {
        $data = [
            'tables_whitelist' => ['table1', 'table2'],
        ];

        $this->assertDataIsValid($data);
    }

    /**
     * Test the tables blacklist.
     */
    public function testTablesBlacklist()
    {
        $data = [
            'tables_blacklist' => ['table1', 'table2'],
        ];

        $this->assertDataIsValid($data);
    }

    /**
     * Test the data converters.
     */
    public function testDataConverters()
    {
        $data = [
            'tables' => [
                'table1' => [
                    'converters' => [
                        'email' => [
                            'converter' => 'randomizeEmail',
                            'unique' => true,
                            'cache_key' => 'user_email',
                        ],
                        'firstname' => [
                            'converter' => 'anonymizeText',
                        ],
                        'lastname' => [
                            'disabled' => true,
                        ],
                        'additional_info' => [
                            'converter' => 'jsonData',
                            'parameters' => [
                                'user.phone' => 'anonymizeNumber',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertDataIsValid($data);
    }

    /**
     * Test the data filters.
     */
    public function testDataFilters()
    {
        $data = [
            'tables' => [
                'table1' => [
                    'limit' => 100,
                    'orderBy' => 'id desc',
                    'filters' => [
                        ['email', 'like', '%@example.org'],
                        ['created_at', 'gt', 'expr: date_sub(now(), interval 60 day)'],
                    ],
                ],
            ],
        ];

        $this->assertDataIsValid($data);
    }

    public function testInvalidSection()
    {
        $data = [
            'not_exists' => ['table1'],
        ];

        $this->assertDataIsNotValid($data);
    }

    /**
     * Test if it is possible to use empty objects/arrays.
     */
    public function testEmptyData()
    {
        $data = new stdClass();
        $this->assertDataIsValid($data);

        $data = [
            'database' => new stdClass(),
            'dump' => new stdClass(),
            'tables_blacklist' => [],
            'tables_whitelist' => [],
            'tables' => new stdClass(),
        ];
        $this->assertDataIsValid($data);
    }

    /**
     * Assert that the config data is valid.
     *
     * @param mixed $data
     */
    private function assertDataIsValid($data)
    {
        $result = $this->validator->validate($data);
        $this->assertTrue($result->isValid());
    }

    /**
     * Assert that the config data is invalid.
     *
     * @param mixed $data
     */
    private function assertDataIsNotValid($data)
    {
        $result = $this->validator->validate($data);
        $this->assertFalse($result->isValid());
    }
}
