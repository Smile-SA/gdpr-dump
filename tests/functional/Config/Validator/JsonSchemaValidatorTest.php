<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional\Config\Validator;

use PDO;
use Smile\GdprDump\Config\Validator\JsonSchemaValidator;
use Smile\GdprDump\Tests\Functional\TestCase;
use stdClass;

class JsonSchemaValidatorTest extends TestCase
{
    private JsonSchemaValidator $validator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $schemaFile = $this->getBasePath() . '/app/config/schema.json';
        $this->validator = new JsonSchemaValidator($schemaFile);
    }

    /**
     * Test the database settings.
     */
    public function testDatabaseSettings(): void
    {
        $data = $this->prepareData([
            'database' => [
                'name' => 'mydb',
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
        ]);
        $this->assertDataIsValid($data);

        // Check if the validation fails when an invalid driver is used
        $data = $this->prepareData(['database' => ['driver' => 'not_exists']]);
        $this->assertDataIsNotValid($data);

        // Check if the validation fails when an unknown parameter is used
        $data = $this->prepareData(['database' => ['not_exists' => true]]);
        $this->assertDataIsNotValid($data);

        // Check if the validation fails when a parameter has the wrong type
        $data = $this->prepareData(['database' => ['charset' => 1.5]]);
        $this->assertDataIsNotValid($data);
    }

    /**
     * Test the dump settings.
     */
    public function testDumpSettings(): void
    {
        $data = $this->prepareData([
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
        ]);
        $this->assertDataIsValid($data);

        // Check if the validation fails when an invalid driver is used
        $data = $this->prepareData(['dump' => ['compress' => 'not_exists']]);
        $this->assertDataIsNotValid($data);

        // Check if the validation fails when an unknown parameter is used
        $data = $this->prepareData(['dump' => ['not_exists' => true]]);
        $this->assertDataIsNotValid($data);

        // Check if the validation fails when a parameter has the wrong type
        $data = $this->prepareData(['dump' => ['output' => 1.5]]);
        $this->assertDataIsNotValid($data);
    }

    /**
     * Test the tables whitelist.
     */
    public function testTablesWhitelist(): void
    {
        $data = $this->prepareData([
            'tables_whitelist' => ['table1', 'table2'],
        ]);

        $this->assertDataIsValid($data);
    }

    /**
     * Test the tables blacklist.
     */
    public function testTablesBlacklist(): void
    {
        $data = $this->prepareData([
            'tables_blacklist' => ['table1', 'table2'],
        ]);

        $this->assertDataIsValid($data);
    }

    /**
     * Test the data converters.
     */
    public function testDataConverters(): void
    {
        $data = $this->prepareData([
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
                            'converter' => 'anonymizeText',
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
        ]);

        $this->assertDataIsValid($data);
    }

    /**
     * Test the data filters.
     */
    public function testDataFilters(): void
    {
        $data = $this->prepareData([
            'tables' => [
                'table1' => [
                    'limit' => 100,
                    'order_by' => 'id desc',
                    'filters' => [
                        ['email', 'like', '%@example.org'],
                        ['created_at', 'gt', 'expr: date_sub(now(), interval 60 day)'],
                    ],
                ],
            ],
        ]);

        $this->assertDataIsValid($data);
    }

    /**
     * Test if it is possible to use empty objects/arrays.
     */
    public function testEmptyData(): void
    {
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
     * Test if the validation fails when the database parameter is not defined.
     */
    public function testDatabaseRequirement(): void
    {
        $this->assertDataIsNotValid(new stdClass());
    }

    /**
     * Test if the validation fails when an invalid section is defined.
     */
    public function testInvalidSection(): void
    {
        $data = $this->prepareData([
            'not_exists' => ['table1'],
        ]);

        $this->assertDataIsNotValid($data);
    }

    /**
     * Test if the validation fails when the config is not an array.
     */
    public function testInvalidRootType(): void
    {
        $this->assertDataIsNotValid('not_an_object');
    }

    /**
     * Add required data to the config params.
     */
    private function prepareData(array $data): array
    {
        // Database object must be defined
        if (!array_key_exists('database', $data)) {
            $data['database'] = new stdClass();
        }

        return $data;
    }

    /**
     * Assert that the config data is valid.
     */
    private function assertDataIsValid(mixed $data): void
    {
        $result = $this->validator->validate($data);
        $this->assertTrue($result->isValid());
    }

    /**
     * Assert that the config data is invalid.
     */
    private function assertDataIsNotValid(mixed $data): void
    {
        $result = $this->validator->validate($data);
        $this->assertFalse($result->isValid());
    }
}
