<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional\Dumper;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Converter\ConverterFactory;
use Smile\GdprDump\Dumper\SqlDumper;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Smile\GdprDump\Tests\Functional\DatabaseTestCase;
use Symfony\Component\Yaml\Yaml;

class SqlDumperTest extends DatabaseTestCase
{
    /**
     * @var string
     */
    private $dumpFile;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->dumpFile = $this->getResource('db/dump.sql');
    }

    /**
     * Test if a dump file is created.
     */
    public function testDumper()
    {
        $config = $this->createConfig();
        $dumper = $this->createDumper();

        // Make sure the dump file does not already exist
        @unlink($this->dumpFile);

        // Create the dump
        $dumper->dump($config);

        // Check if the file was created
        $this->assertFileExists($this->dumpFile);

        // Delete the file
        if (file_exists($this->dumpFile)) {
            @unlink($this->dumpFile);
        }
    }

    /**
     * Create the config object.
     *
     * @return Config
     */
    private function createConfig(): Config
    {
        $data = Yaml::parseFile($this->getTestConfigFile());
        $data['dump']['output'] = $this->dumpFile;

        return new Config($data);
    }

    /**
     * Create a SQL dumper object.
     *
     * @return SqlDumper
     */
    private function createDumper(): SqlDumper
    {
        $converterFactoryMock = $this->createMock(ConverterFactory::class);
        $converterFactoryMock->method('create')
            ->willReturn(new ConverterMock());

        /** @var ConverterFactory $converterFactoryMock */
        return new SqlDumper($converterFactoryMock);
    }
}
