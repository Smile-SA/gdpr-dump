<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Compiler\Processor;

use Smile\GdprDump\Configuration\Compiler\Processor\DatabaseUrlProcessor;
use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Configuration\Loader\Container;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

final class DatabaseUrlProcessorTest extends TestCase
{
    /**
     * Assert that the url is processed and individual params (e.g. password) take precedence.
     */
    public function testProcessor(): void
    {
        $container = new Container(
            (object) [
                'database' => (object) [
                    'password' => 'another_secret_password',
                    'url' => 'mysql://foo:secret_password@localhost/database_name',
                ],
            ]
        );

        $processor = new DatabaseUrlProcessor();
        $processor->process($container);

        $dbParams = $container->get('database');
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
        $container = new Container(
            (object) [
                'database' => (object) [
                    'url' => 'mysql://localhost',
                ],
            ]
        );

        $processor = new DatabaseUrlProcessor();
        $processor->process($container);

        $dbParams = $container->get('database');
        $this->assertObjectHasProperty('host', $dbParams);
        $this->assertObjectNotHasProperty('url', $dbParams);
        $this->assertSame('localhost', $dbParams->host);
    }

    /**
     * Assert that an exception is thrown when an invalid url is specified.
     */
    public function testInvalidUrl(): void
    {
        $container = new Container(
            (object) [
                'database' => (object) [
                    'url' => 'invalid',
                ],
            ]
        );

        $processor = new DatabaseUrlProcessor();
        $this->expectException(UnexpectedValueException::class);
        $processor->process($container);
    }

    /**
     * Assert that an exception is thrown when an invalid driver is specified.
     */
    public function testInvalidDriver(): void
    {
        $container = new Container(
            (object) [
                'database' => (object) [
                    'url' => 'invalid://foo:secret_password@localhost/database_name',
                ],
            ]
        );

        $processor = new DatabaseUrlProcessor();
        $this->expectException(ParseException::class);
        $processor->process($container);
    }
}
