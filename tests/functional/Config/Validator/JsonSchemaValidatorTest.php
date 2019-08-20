<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional\Config;

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
    public function setUp()
    {
        $this->validator = new JsonSchemaValidator(APP_ROOT . '/config/schema.json');
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
                'pdo_settings' => [
                    1001 => true,
                ]
            ],
        ];

        $this->assertDataIsValid($data);

        $data['database']['not_exists'] = true;
        $this->assertDataIsNotValid($data);
    }

    /**
     * Test the dump settings
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

        $data['dump']['not_exists'] = true;
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

    public function testInvalidSection()
    {
        $data = [
            'notExists' => ['table1'],
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
     * Test with an invalid database driver.
     */
    public function testInvalidDatabaseDriver()
    {
        $data = [
            'database' => [
                'driver' => 'not_exists',
            ],
        ];

        $this->assertDataIsNotValid($data);
    }

    /**
     * Test with an invalid compression algorithm.
     */
    public function testInvalidCompression()
    {
        $data = [
            'dump' => [
                'compress' => 'not_exists',
            ],
        ];

        $this->assertDataIsNotValid($data);
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
