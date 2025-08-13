<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\EventListener;

use Smile\GdprDump\Configuration\Event\ConfigurationParsedEvent;
use Smile\GdprDump\Configuration\EventListener\DatabaseUrlListener;
use Smile\GdprDump\Configuration\Exception\ConfigLoadException;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

final class DatabaseUrlListenerTest extends TestCase
{
    /**
     * Assert that the url is processed and individual params (e.g. password) take precedence.
     */
    public function testDatabaseUrlListener(): void
    {
        $data = (object) [
            'database' => (object) [
                'password' => 'another_secret_password',
                'url' => 'mysql://foo:secret_password@localhost/database_name',
            ],
        ];

        $listener = new DatabaseUrlListener();
        $listener(new ConfigurationParsedEvent($data));

        $dbParams = $data->database;
        $this->assertObjectHasProperty('name', $dbParams);
        $this->assertObjectHasProperty('host', $dbParams);
        $this->assertObjectHasProperty('user', $dbParams);
        $this->assertObjectHasProperty('password', $dbParams);
        $this->assertObjectNotHasProperty('port', $dbParams);
        $this->assertObjectNotHasProperty('url', $dbParams);

        $this->assertSame('database_name', $dbParams->name);
        $this->assertSame('localhost', $dbParams->host);
        $this->assertSame('foo', $dbParams->user);
        $this->assertSame('another_secret_password', $dbParams->password);
    }

    /**
     * Assert that the url is processed properly when only the scheme and path are defined.
     */
    public function testUrlWithoutDatabaseName(): void
    {
        $data = (object) [
            'database' => (object) [
                'url' => 'mysql://localhost',
            ],
        ];

        $listener = new DatabaseUrlListener();
        $listener(new ConfigurationParsedEvent($data));

        $dbParams = $data->database;
        $this->assertObjectHasProperty('host', $dbParams);
        $this->assertObjectNotHasProperty('url', $dbParams);
        $this->assertSame('localhost', $dbParams->host);
    }

    /**
     * Assert that an exception is thrown when an invalid url is specified.
     */
    public function testInvalidUrl(): void
    {
        $data = (object) [
            'database' => (object) [
                'url' => 'invalid',
            ],
        ];

        $listener = new DatabaseUrlListener();
        $this->expectException(UnexpectedValueException::class);
        $listener(new ConfigurationParsedEvent($data));
    }

    /**
     * Assert that an exception is thrown when an invalid driver is specified.
     */
    public function testInvalidDriver(): void
    {
        $data = (object) [
            'database' => (object) [
                'url' => 'invalid://foo:secret_password@localhost/database_name',
            ],
        ];

        $listener = new DatabaseUrlListener();
        $this->expectException(ConfigLoadException::class);
        $listener(new ConfigurationParsedEvent($data));
    }

    /**
     * Assert that an exception is thrown when the `database` parameter has an invalid type.
     */
    public function testInvalidDatabaseType(): void
    {
        $data = (object) ['database' => 'not an object'];

        $listener = new DatabaseUrlListener();
        $this->expectException(ConfigLoadException::class);
        $listener(new ConfigurationParsedEvent($data));
    }
}
