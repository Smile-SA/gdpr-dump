<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional\Configuration;

use Smile\GdprDump\Configuration\ConfigurationBuilder;
use Smile\GdprDump\Configuration\ConfigurationFactory;
use Smile\GdprDump\Configuration\Loader\Resource\Resource;
use Smile\GdprDump\Tests\Functional\TestCase;

final class ConfigurationBuilderTest extends TestCase
{
    /**
     * @var array<string, string>
     */
    private array $previousEnvVars;

    /**
     * Assert that processors are executed in the expected order.
     */
    public function testProcessorsOrder(): void
    {
        $this->initEnvVars([
            'TEST_DUMP_OUTPUT' => 'dump.sql',
            'TEST_DATABASE_URL' => 'mysql://localhost/db_name',
            'TEST_VERSION' => '2.5',
        ]);

        $builder = $this->createConfigurationBuilder();
        $builder->addResource(new Resource(self::getResource('config/test_listener_order/config.yaml')));
        $configuration = $builder->build();

        // Assert that env vars and if_version sections were resolved before DumpOutputProcessor
        $dump = $configuration->getDumpSettings();
        $this->assertSame('dump.sql', $dump->getOutput());

        //  Assert that env vars and if_version sections were resolved before DatabaseUrlProcessor
        $connectionParams = $configuration->getConnectionParams();
        $this->assertArrayHasKey('host', $connectionParams);
        $this->assertSame('localhost', $connectionParams['host']);
        $this->assertArrayHasKey('dbname', $connectionParams);
        $this->assertSame('db_name', $connectionParams['dbname']);

        $this->restoreEnvVars();
    }

    /**
     * Create a configuration builder.
     */
    private function createConfigurationBuilder(): ConfigurationBuilder
    {
        /** @var ConfigurationFactory $factory */
        $factory = $this->getContainer()->get(ConfigurationFactory::class);

        return $factory->createBuilder();
    }

    /**
     * Init environment variables required for this test.
     *
     * @param array<string, string> $values
     */
    private function initEnvVars(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->previousEnvVars[$key] = (string) getenv($key);
            putenv($key . '=' . $value);
        }
    }

    /**
     * Restore value of env vars.
     */
    private function restoreEnvVars(): void
    {
        foreach ($this->previousEnvVars as $param => $value) {
            putenv($param . '=' . $value);
        }
    }
}
