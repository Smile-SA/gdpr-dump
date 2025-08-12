<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\EventListener;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\Event\LoadedEvent;
use Smile\GdprDump\Config\EventListener\DatabaseUrlListener;
use Smile\GdprDump\Config\Validator\ValidationException;
use Smile\GdprDump\Tests\Unit\TestCase;

final class DatabaseUrlListenerTest extends TestCase
{
    /**
     * Assert that the url is processed and individual params (e.g. password) take precedence.
     */
    public function testDatabaseUrlListener(): void
    {
        $data = [
            'database' => [
                'password' => 'another_secret_password',
                'url' => 'mysql://foo:secret_password@localhost/database_name',
            ],
        ];

        $config = new Config($data);
        $listener = new DatabaseUrlListener();
        $listener(new LoadedEvent($config));

        $dbParams = $config->get('database');
        $this->assertArrayHasKey('name', $dbParams);
        $this->assertArrayHasKey('host', $dbParams);
        $this->assertArrayHasKey('user', $dbParams);
        $this->assertArrayHasKey('password', $dbParams);
        $this->assertArrayNotHasKey('port', $dbParams);

        $this->assertSame('database_name', $dbParams['name']);
        $this->assertSame('localhost', $dbParams['host']);
        $this->assertSame('foo', $dbParams['user']);
        $this->assertSame('another_secret_password', $dbParams['password']);
    }

    /**
     * Assert that the url is processed properly when only the scheme and path are defined.
     */
    public function testUrlWithoutDatabaseName(): void
    {
        $data = [
            'database' => [
                'url' => 'mysql://localhost',
            ],
        ];

        $config = new Config($data);
        $listener = new DatabaseUrlListener();
        $listener(new LoadedEvent($config));

        $dbParams = $config->get('database');
        $this->assertSame('localhost', $dbParams['host']);
    }

    /**
     * Assert that an exception is thrown when an invalid url is specified.
     */
    public function testInvalidUrl(): void
    {
        $data = [
            'database' => [
                'url' => 'invalid',
            ],
        ];

        $config = new Config($data);
        $listener = new DatabaseUrlListener();

        $this->expectException(ValidationException::class);
        $listener(new LoadedEvent($config));
    }

    /**
     * Assert that an exception is thrown when an invalid driver is specified.
     */
    public function testInvalidDriver(): void
    {
        $data = [
            'database' => [
                'url' => 'invalid://foo:secret_password@localhost/database_name',
            ],
        ];

        $config = new Config($data);
        $listener = new DatabaseUrlListener();

        $this->expectException(ValidationException::class);
        $listener(new LoadedEvent($config));
    }

    /**
     * Assert that an exception is thrown when the `database` parameter has an invalid type.
     */
    public function testInvalidDatabaseType(): void
    {
        $config = new Config(['database' => 'not an array']);
        $listener = new DatabaseUrlListener();

        $this->expectException(ValidationException::class);
        $listener(new LoadedEvent($config));
    }
}
