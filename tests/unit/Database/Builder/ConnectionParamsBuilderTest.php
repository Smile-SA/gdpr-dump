<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Database\Builder;

use PDO;
use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Database\Builder\ConnectionParamsBuilder;
use Smile\GdprDump\Database\DatabaseInterface;
use Smile\GdprDump\Tests\Unit\TestCase;
use Smile\GdprDump\Util\ArrayHelper;

final class ConnectionParamsBuilderTest extends TestCase
{
    /**
     * Test the builder when all database settings are defined.
     */
    public function testAllSettings(): void
    {
        $settings = [
            'name' => 'test_db',
            'user' => 'test_user',
            'password' => 'test_password',
            'host' => 'db',
            'port' => 3306,
            'charset' => 'utf8mb4',
            'unix_socket' => '/tmp/mysql.sock',
            'driver' => DatabaseInterface::DRIVER_MYSQL,
            'driver_options' => [
                PDO::MYSQL_ATTR_SSL_KEY => 'key.pem',
                PDO::MYSQL_ATTR_SSL_CERT => 'cert.pem',
                PDO::MYSQL_ATTR_SSL_CA => 'ca-cert.pem',
            ],
        ];

        $result = $this->createBuilder()->build($this->createConfig($settings));
        $this->assertSameKeyValuePairs($this->getExpectedResult($settings), $result);
    }

    /**
     * Test the builder when only a few database settings are defined.
     */
    public function testPartialSettings(): void
    {
        $settings = [
            'name' => 'test_db',
            'user' => 'test_user',
            'password' => 'test_password',
            'host' => 'db',
        ];

        $result = $this->createBuilder()->build($this->createConfig($settings));
        $this->assertSameKeyValuePairs($this->getExpectedResult($settings), $result);
    }

    /**
     * Test the builder with empty database settings.
     */
    public function testEmptySettings(): void
    {
        $result = $this->createBuilder()->build($this->createConfig());
        $this->assertSameKeyValuePairs($this->getExpectedResult(), $result);
    }

    /**
     * Get the expected value returned by the builder.
     */
    private function getExpectedResult(array $databaseSettings = []): array
    {
        if (array_key_exists('name', $databaseSettings)) {
            $databaseSettings['dbname'] = $databaseSettings['name'];
            unset($databaseSettings['name']);
        }

        if (array_key_exists('driver_options', $databaseSettings)) {
            $databaseSettings['driverOptions'] = $databaseSettings['driver_options'];
            unset($databaseSettings['driver_options']);
        }

        return $databaseSettings;
    }

    /**
     * Create a config object with the specified database settings.
     */
    private function createConfig(array $databaseSettings = []): Config
    {
        return new Config(['database' => $databaseSettings]);
    }

    /**
     * Create the object to test.
     */
    private function createBuilder(): ConnectionParamsBuilder
    {
        return new ConnectionParamsBuilder(new ArrayHelper());
    }
}
