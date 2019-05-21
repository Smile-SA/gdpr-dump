<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Dumper\Sql;

use Smile\Anonymizer\Config\Config;
use Smile\Anonymizer\Converter\ConverterFactory;
use Smile\Anonymizer\Dumper\SqlDumper;
use Smile\Anonymizer\Tests\Converter\Dummy;
use Smile\Anonymizer\Tests\DbTestCase;

class SqlDumperTest extends DbTestCase
{
    /**
     * Test if a dump file is created.
     */
    public function testDumper()
    {
        // Make sure the dump file does not already exist
        $dumpFile = APP_ROOT . '/tests/Resources/db/test_db_dump.sql';
        @unlink($dumpFile);

        // Initialize a sample config
        $config = new Config([
            'dump' => [
                'output' => $dumpFile,
            ],
            'database' => $this->getConnectionParams(),
            'tables' => [
                'customers' => [
                    'converters' => [
                        'customers' => [
                            'email' => new Dummy(),
                        ],
                    ],
                ],
            ],
        ]);

        $dumper = $this->createDumper();

        $dumper->dump($config);
        $this->assertFileExists($dumpFile);
    }

    /**
     * Create a SQL dumper object.
     *
     * @return SqlDumper
     */
    private function createDumper(): SqlDumper
    {
        /** @var ConverterFactory $converterFactoryMock */
        $converterFactoryMock = $this->createMock(ConverterFactory::class);
        $converterFactoryMock->method('create')
            ->willReturn(new Dummy());

        $sqlDumper = new SqlDumper($converterFactoryMock);

        return $sqlDumper;
    }
}
