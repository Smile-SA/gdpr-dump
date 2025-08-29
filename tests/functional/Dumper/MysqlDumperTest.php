<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional\Dumper;

use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Dumper\DumperResolver;
use Smile\GdprDump\Dumper\Exception\DumpException;
use Smile\GdprDump\Dumper\MysqlDumper;
use Smile\GdprDump\Tests\Functional\TestCase;

final class MysqlDumperTest extends TestCase
{
    private string $dumpFile;

    protected function setUp(): void
    {
        $this->dumpFile = $this->getResource('var/dump.sql');
    }

    /**
     * Test if a dump file is created.
     */
    public function testDumper(): void
    {
        // Make sure the dump file does not already exist
        $this->deleteDumpFile();

        // Create the dump
        $configuration = $this->createConfiguration();
        $this->assertSame('fr_FR', $configuration->getFakerConfig()->getLocale());
        $this->getDumper()->dump($configuration);
        $this->assertDumpIsValid();

        // Same tests but with filter propagation disabled
        $configuration = $this->createConfiguration();
        $configuration->getFilterPropagationConfig()->setEnabled(false);

        $this->getDumper()->dump($configuration);
        $this->assertDumpIsValid(false);
    }

    /**
     * Assert that the dry run option works properly.
     */
    public function testDryRun(): void
    {
        // Make sure the dump file does not already exist
        $this->deleteDumpFile();

        // Run the dumper with dry run option
        $configuration = $this->createConfiguration();
        $this->getDumper()->dump($configuration, true);
        $this->assertFileDoesNotExist($this->dumpFile);
    }

    /**
     * Assert that an exception is thrown when strict mode is enabled.
     */
    public function testStrictMode(): void
    {
        // Make sure the dump file does not exist
        $this->deleteDumpFile();

        // Run the dumper with strict mode
        $configuration = $this->createConfiguration();
        $configuration->setStrictSchema(true);
        $this->expectException(DumpException::class);
        $this->expectExceptionMessageMatches('/No table found with pattern "not_exists"/');
        $this->getDumper()->dump($configuration, true);
    }

    /**
     * Assert that the dump file contents match the dump configuration file.
     */
    private function assertDumpIsValid(bool $filterPropagationEnabled = true): void
    {
        // Check if the file was created
        $this->assertFileExists($this->dumpFile);

        // Make sure the file contains the dump output
        $output = (string) file_get_contents($this->dumpFile);
        unlink($this->dumpFile);
        $this->assertNotEmpty($output);

        // Assert that the dump only includes allowed tables
        $this->assertStringContainsString('CREATE TABLE `customers`', $output);
        $this->assertStringContainsString('CREATE TABLE `stores`', $output);
        $this->assertStringContainsString('CREATE TABLE `addresses`', $output);
        $this->assertStringNotContainsString('CREATE TABLE `config`', $output);

        // store3 must not be included in the dump
        $this->assertStringContainsString('store1', $output);
        $this->assertStringContainsString('store2', $output);
        $this->assertStringNotContainsString('store3', $output);

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
        if ($filterPropagationEnabled) {
            $this->assertStringNotContainsString('user5@test.org', $output);
        } else {
            $this->assertStringContainsString('user5@test.org', $output);
        }

        // Only the addresses of dumped users must be included
        $this->assertStringContainsString('street3', $output);
        $this->assertStringContainsString('street4', $output);
        $this->assertStringContainsString('street5', $output);
        $this->assertStringContainsString('street6', $output);
        $this->assertStringContainsString('street7', $output);

        if ($filterPropagationEnabled) {
            $this->assertStringNotContainsString('street1', $output);
            $this->assertStringNotContainsString('street2', $output);
            $this->assertStringNotContainsString('street8', $output);
            $this->assertStringNotContainsString('street9', $output);
        } else {
            $this->assertStringContainsString('street1', $output);
            $this->assertStringContainsString('street2', $output);
            $this->assertStringContainsString('street8', $output);
            $this->assertStringContainsString('street9', $output);
        }
    }

    /**
     * Delete the dump file if it exists.
     */
    private function deleteDumpFile(): void
    {
        if (file_exists($this->dumpFile)) {
            unlink($this->dumpFile);
        }
    }

    /**
     * Create the config object.
     */
    private function createConfiguration(): Configuration
    {
        $configuration = clone $this->getConfiguration();
        $dumpSettings = $configuration->getDumpSettings();
        $dumpSettings->setOutput($this->dumpFile);

        return $configuration;
    }

    /**
     * Create a SQL dumper object.
     */
    private function getDumper(): MysqlDumper
    {
        /** @var DumperResolver $resolver */
        $resolver = $this->getContainer()->get(DumperResolver::class);

        /** @var MysqlDumper */
        return $resolver->getDumper($this->getConfiguration());
    }
}
