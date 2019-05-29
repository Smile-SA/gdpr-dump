<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Dumper;

use Smile\Anonymizer\Config\Config;
use Smile\Anonymizer\Converter\ConverterFactory;
use Smile\Anonymizer\Dumper\SqlDumper;
use Smile\Anonymizer\Tests\Converter\Dummy;
use Smile\Anonymizer\Tests\DbTestCase;
use Symfony\Component\Yaml\Yaml;

class SqlDumperTest extends DbTestCase
{
    /**
     * Test if a dump file is created.
     */
    public function testDumper()
    {
        $config = $this->createConfig();
        $dumper = $this->createDumper();

        // Make sure the dump file does not already exist
        $dumpFile = $config->get('dump.output');
        @unlink($dumpFile);

        // Create the dump
        $dumper->dump($config);

        // Check if the file was created
        $this->assertFileExists($dumpFile);
        @unlink($dumpFile);
    }

    /**
     * Create the config object.
     *
     * @return Config
     */
    private function createConfig(): Config
    {
        $data = Yaml::parseFile($this->getResource('config/test_config.yaml'));
        $data['dump']['output'] = $this->getResource('db/test_db_dump.sql');

        return new Config($data);
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
