<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional\Dumper;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Dumper\SqlDumper;
use Smile\GdprDump\Faker\FakerService;
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
    public static function setUpBeforeClass(): void
    {
        static::bootDatabase();
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->dumpFile = $this->getResource('db/dump.sql');
    }

    /**
     * Test if a dump file is created.
     */
    public function testDumper(): void
    {
        $config = $this->createConfig();
        $dumper = $this->createDumper();

        // Make sure the dump file does not already exist
        if (file_exists($this->dumpFile)) {
            unlink($this->dumpFile);
        }

        /** @var FakerService $faker */
        $faker = $this->getContainer()->get('faker.service');
        $this->assertSame('en_US', $faker->getLocale());

        // Create the dump
        $dumper->dump($config);
        $this->assertDumpIsValid();

        // Assert that the faker locale was changed
        $this->assertSame('fr_FR', $faker->getLocale());
    }

    /**
     * Assert that the dump file contents match the dump configuration file.
     */
    private function assertDumpIsValid(): void
    {
        // Check if the file was created
        $this->assertFileExists($this->dumpFile);

        // Make sure the file contains the dump output
        $output = file_get_contents($this->dumpFile);
        unlink($this->dumpFile);
        $this->assertNotEmpty($output);

        // Assert that only whitelisted tables are included in the dump
        $this->assertStringContainsString('CREATE TABLE `customers`', $output);
        $this->assertStringContainsString('CREATE TABLE `stores`', $output);
        $this->assertStringNotContainsString('CREATE TABLE `addresses`', $output);

        // User 1 must not be dumped (does not match the date filter)
        $this->assertStringNotContainsString('user1@test.org', $output);

        // User 2 must be dumped, but not anonymized (skip_conversion_if parameter)
        $this->assertStringContainsString('user2@test.org', $output);
        $this->assertStringContainsString('firstname2', $output);
        $this->assertStringContainsString('lastname2', $output);
        $this->assertStringNotContainsString('test_user2@test.org', $output);
        $this->assertStringNotContainsString('test_firstname2', $output);
        $this->assertStringNotContainsString('test_lastname2', $output);

        // User 3 must be dumped and anonymized
        $this->assertStringContainsString('test_user3@test.org', $output);
        $this->assertStringContainsString('test_firstname3', $output);
        $this->assertStringContainsString('test_lastname3', $output);

        // User 4 must be dumped and anonymized, except email (condition parameter)
        $this->assertStringContainsString('user4@test.org', $output);
        $this->assertStringContainsString('test_firstname4', $output);
        $this->assertStringContainsString('test_lastname4', $output);
        $this->assertStringNotContainsString('test_user4@test.org', $output);

        // User 5 must not be dumped (store id condition not matched)
        $this->assertStringNotContainsString('user5@test.org', $output);
    }

    /**
     * Create the config object.
     *
     * @return Config
     */
    private function createConfig(): Config
    {
        /** @var Config $config */
        $config = $this->getContainer()->get('dumper.config');

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
        /** @var SqlDumper $dumper */
        $dumper = $this->getContainer()->get('dumper');

        return $dumper;
    }
}
