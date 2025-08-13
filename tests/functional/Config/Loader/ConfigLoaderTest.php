<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional\Config\Loader;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\Loader\ConfigLoader;
use Smile\GdprDump\Config\Loader\ConfigLoaderInterface;
use Smile\GdprDump\Tests\Functional\TestCase;

final class ConfigLoaderTest extends TestCase
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
            'TEST_VERSION' => '10.5',
        ]);

        $config = new Config();
        $loader = $this->getConfigLoader();
        $fileName = self::getResource('config/test_listener_order/config.yaml');
        $loader->load($config, $fileName);

        // Assert that EnvVarProcessor and VersionProcessor were executed before DumpOutputProcessor
        $dump = $config->get('dump');
        $this->assertIsArray($dump);
        $this->assertArrayHasKey('output', $dump);
        $this->assertSame('dump.sql', $config->get('dump')['output']);

        // Assert that EnvVarProcessor and VersionProcessor were executed before DatabaseUrlProcessor
        $database = $config->get('database');
        $this->assertIsArray($database);
        $this->assertArrayHasKey('host', $database);
        $this->assertSame('localhost', $database['host']);
        $this->assertArrayHasKey('name', $database);
        $this->assertSame('db_name', $database['name']);

        $this->restoreEnvVars();
    }

    /**
     * Get the config compiler.
     */
    private function getConfigLoader(): ConfigLoaderInterface
    {
        /** @var ConfigLoaderInterface */
        return $this->getContainer()->get(ConfigLoader::class);
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
