<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional\Configuration;

use Smile\GdprDump\Configuration\ConfigurationBuilder;
use Smile\GdprDump\Configuration\ConfigurationFactory;
use Smile\GdprDump\Configuration\Exception\JsonSchemaException;
use Smile\GdprDump\Configuration\Loader\Resource\Resource;
use Smile\GdprDump\Tests\Functional\TestCase;

final class ConfigurationBuilderTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $previousEnvVars = [];

    /**
     * Assert that processors are executed in the expected order.
     */
    public function testProcessorsOrder(): void
    {
        $this->initEnvVars();

        $builder = $this->createConfigurationBuilder();
        $configuration = $builder->build();

        // Assert that env vars were resolved before DumpOutputProcessor
        $dump = $configuration->getDumpSettings();
        $this->assertSame('dump.sql', $dump->getOutput());

        //  Assert that env vars were resolved before DatabaseUrlProcessor
        $connectionParams = $configuration->getConnectionParams();
        $this->assertArrayHasKey('host', $connectionParams);
        $this->assertSame('localhost', $connectionParams['host']);
        $this->assertArrayHasKey('dbname', $connectionParams);
        $this->assertSame('db_name', $connectionParams['dbname']);

        // Assert that the `if_version` block was merged and that env vars inside this block were resolved
        $tableConfig = $configuration->getTableConfigs()->get('logs');
        $this->assertNotNull($tableConfig);
        $this->assertTrue($tableConfig->isTruncate());

        $this->restoreEnvVars();
    }

    /**
     * Assert that only tables/variables can be set in if_version parameter.
     */
    public function testIfVersionContainsDisallowedParameter(): void
    {
        $this->initEnvVars();

        $builder = $this->createConfigurationBuilder();
        $builder->addResource(new Resource('{if_version: {\'>=1.0.0\': {dump: {hex_blob: true}}}}', false));
        $this->expectException(JsonSchemaException::class);
        $builder->build();

        $this->restoreEnvVars();
    }

    /**
     * Create a configuration builder.
     */
    private function createConfigurationBuilder(): ConfigurationBuilder
    {
        /** @var ConfigurationFactory $factory */
        $factory = $this->getContainer()->get(ConfigurationFactory::class);

        return $factory
            ->createBuilder()
            ->addResource(new Resource(self::getResource('config/test_config_processor/config.yaml')));
    }

    /**
     * Init environment variables required for this test.
     */
    private function initEnvVars(): void
    {
        $envVars = [
            'TEST_VERSION' => '2.5',
            'TEST_DUMP_OUTPUT' => 'dump.sql',
            'TEST_DATABASE_URL' => 'mysql://localhost/db_name',
            'TEST_TRUNCATE_LOGS' => true,
        ];

        foreach ($envVars as $key => $value) {
            $this->previousEnvVars[$key] = getenv($key);
            putenv($key . '=' . $value);
        }
    }

    /**
     * Restore value of env vars.
     */
    private function restoreEnvVars(): void
    {
        foreach ($this->previousEnvVars as $param => $value) {
            $assignment = $value !== false ? $param . '=' . $value : $param;
            putenv($assignment);
        }
    }
}
