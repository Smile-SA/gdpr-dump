<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Loader\Processor;

use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Configuration\Loader\Processor\DatabaseUrlProcessor;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

final class DatabaseUrlProcessorTest extends TestCase
{
    /**
     * Assert that the url is processed and individual params (e.g. password) take precedence.
     */
    public function testProcessor(): void
    {
        $data = (object) [
            'database' => (object) [
                'password' => 'another_secret_password',
                'url' => 'mysql://foo:secret_password@localhost/database_name',
            ],
        ];

        $processor = new DatabaseUrlProcessor();
        $processor->process($data);

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
        $this->assertSame('another_secret_password', $dbParams->password); // @phpstan-ignore method.alreadyNarrowedType
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

        $processor = new DatabaseUrlProcessor();
        $processor->process($data);

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

        $processor = new DatabaseUrlProcessor();
        $this->expectException(UnexpectedValueException::class);
        $processor->process($data);
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

        $processor = new DatabaseUrlProcessor();
        $this->expectException(ParseException::class);
        $processor->process($data);
    }

    /**
     * Assert that the processor performs no action when the database object is invalid.
     */
    public function testInvalidDatabaseType(): void
    {
        $data = (object) ['database' => 'not an object'];

        $processor = new DatabaseUrlProcessor();
        $clonedData = clone $data;
        $processor->process($clonedData);
        $this->assertEquals($data, $clonedData);
    }

    /**
     * Assert that an exception is thrown when the URL is not a string.
     */
    public function testInvalidUrlType(): void
    {
        $data = (object) [
            'database' => (object) [
                'url' => [],
            ],
        ];

        $processor = new DatabaseUrlProcessor();
        $this->expectException(ParseException::class);
        $processor->process($data);
    }
}
