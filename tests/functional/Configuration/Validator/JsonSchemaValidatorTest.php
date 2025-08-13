<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional\Configuration\Validator;

use PDO;
use Smile\GdprDump\Configuration\Exception\JsonSchemaException;
use Smile\GdprDump\Configuration\Validator\JsonSchemaValidator;
use Smile\GdprDump\Tests\Functional\TestCase;
use stdClass;

final class JsonSchemaValidatorTest extends TestCase
{
    private JsonSchemaValidator $validator;

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
        $data = (object) [
            'database' => (object) [
                'name' => 'mydb',
                'user' => 'myuser',
                'password' => 'mypassword',
                'host' => 'myhost',
                'port' => 3306,
                'driver' => 'pdo_mysql',
                'charset' => 'utf8mb',
                'driver_options' => (object) [
                    PDO::MYSQL_ATTR_LOCAL_INFILE => true,
                ],
            ],
        ];
        $this->assertDataIsValid($data);

        // Check if the validation fails when an invalid driver is used
        $data = (object) ['database' => ['driver' => 'not_exists']];
        $this->assertDataIsNotValid($data);

        // Check if the validation fails when an unknown parameter is used
        $data = (object) ['database' => ['not_exists' => true]];
        $this->assertDataIsNotValid($data);

        // Check if the validation fails when a parameter has the wrong type
        $data = (object) ['database' => ['charset' => 1.5]];
        $this->assertDataIsNotValid($data);
    }

    /**
     * Test the dump settings.
     */
    public function testDumpSettings(): void
    {
        $data = (object) [
            'dump' => (object) [
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
        $data = (object) ['dump' => ['compress' => 'not_exists']];
        $this->assertDataIsNotValid($data);

        // Check if the validation fails when an unknown parameter is used
        $data = (object) ['dump' => ['not_exists' => true]];
        $this->assertDataIsNotValid($data);

        // Check if the validation fails when a parameter has the wrong type
        $data = (object) ['dump' => ['output' => 1.5]];
        $this->assertDataIsNotValid($data);
    }

    /**
     * Test the "tables_whitelist" parameter.
     */
    public function testTablesWhitelist(): void
    {
        $data = (object) ['tables_whitelist' => ['table1', 'table2']];
        $this->assertDataIsValid($data);
    }

    /**
     * Test the "tables_blacklist" parameter.
     */
    public function testTablesBlacklist(): void
    {
        $data = (object) ['tables_blacklist' => ['table1', 'table2']];
        $this->assertDataIsValid($data);
    }

    /**
     * Test the data converters.
     */
    public function testDataConverters(): void
    {
        $data = (object) [
            'tables' => (object) [
                'table1' => (object) [
                    'converters' => (object) [
                        'email' => (object) [
                            'converter' => 'randomizeEmail',
                            'unique' => true,
                            'cache_key' => 'user_email',
                        ],
                        'firstname' => (object) [
                            'converter' => 'anonymizeText',
                        ],
                        'lastname' => (object) [
                            'converter' => 'anonymizeText',
                            'disabled' => true,
                        ],
                        'additional_info' => (object) [
                            'converter' => 'jsonData',
                            'parameters' => (object) [
                                'converters' => (object) [
                                    'user.phone' => (object) [
                                        'converter' => 'anonymizeNumber',
                                    ],
                                ],
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
    public function testDataFilters(): void
    {
        $data = (object) [
            'tables' => (object) [
                'table1' => (object) [
                    'limit' => 100,
                    'order_by' => 'id desc',
                    'where' => '1=1',
                ],
            ],
        ];

        $this->assertDataIsValid($data);
    }

    /**
     * Test if it is possible to use empty objects/arrays.
     */
    public function testEmptyData(): void
    {
        $data = (object) [
            'database' => new stdClass(),
            'dump' => new stdClass(),
            'tables_blacklist' => [],
            'tables_whitelist' => [],
            'tables' => new stdClass(),
        ];
        $this->assertDataIsValid($data);
    }

    /**
     * Test if the validation fails when an invalid section is defined.
     */
    public function testInvalidSection(): void
    {
        $data = (object) ['not_exists' => ['table1']];
        $this->assertDataIsNotValid($data);
    }

    /**
     * Assert that the config data is valid.
     */
    private function assertDataIsValid(mixed $data): void
    {
        try {
            $this->validator->validate($data);
            $valid = true;
        } catch (JsonSchemaException) {
            $valid = false;
        }

        $this->assertTrue($valid);
    }

    /**
     * Assert that the config data is invalid.
     */
    private function assertDataIsNotValid(mixed $data): void
    {
        try {
            $this->validator->validate($data);
            $valid = true;
        } catch (JsonSchemaException) {
            $valid = false;
        }

        $this->assertFalse($valid);
    }
}
