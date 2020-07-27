<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional\Dumper;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Converter\ConverterFactory;
use Smile\GdprDump\Dumper\SqlDumper;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Smile\GdprDump\Tests\Functional\TestCase;

class SqlDumperTest extends TestCase
{
    /**
     * @var string
     */
    private $dumpFile;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        static::bootDatabase();
    }

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
        $this->assertDumpIsValid();
    }

    /**
     * Assert that the dump file contents match the dump configuration file.
     */
    private function assertDumpIsValid()
    {
        // Check if the file was created
        $this->assertFileExists($this->dumpFile);

        // Make sure the file contains the dump output
        $output = file_get_contents($this->dumpFile);
        @unlink($this->dumpFile);
        $this->assertNotEmpty($output);

        // Assert that only whitelisted tables are included in the dump
        $this->assertContains('CREATE TABLE `customers`', $output);
        $this->assertContains('CREATE TABLE `stores`', $output);
        $this->assertNotContains('CREATE TABLE `addresses`', $output);

        // User 1 must not be dumped (does not match the date filter)
        $this->assertNotContains('user1@test.org', $output);

        // User 2 must be dumped, but not anonymized (skip_conversion_if parameter)
        $this->assertContains('user2@test.org', $output);
        $this->assertNotContains('test_user2@test.org', $output);
        $this->assertContains('firstname2', $output);
        $this->assertNotContains('test_firstname2', $output);

        // Users 3 and 4 should be anonymized and dumped
        $this->assertContains('test_user3@test.org', $output);
        $this->assertContains('test_firstname3', $output);
        $this->assertContains('test_user4@test.org', $output);
        $this->assertContains('test_firstname4', $output);

        // User 5 must not be dumped (store id condition not matched)
        $this->assertNotContains('user5@test.org', $output);
    }

    /**
     * Create the config object.
     *
     * @return Config
     */
    private function createConfig(): Config
    {
        /** @var Config $config */
        $config = $this->getContainer()->get(Config::class);

        $dumpParams = $config->get('dump');
        $dumpParams['output'] = $this->dumpFile;
        $config->set('dump', $dumpParams);

        return $config;
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
