<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Compiler\Processor;

use Smile\GdprDump\Config\Compiler\CompileException;
use Smile\GdprDump\Config\Compiler\Processor\DatabaseUrlProcessor;
use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Tests\Unit\TestCase;

class DatabaseUrlProcessorTest extends TestCase
{
    /**
     * Assert that the url is processed and individual params (e.g. password) take precedence.
     */
    public function testDatabaseUrlProcessor(): void
    {
        $data = [
            'database' => [
                'password' => 'another_secret_password',
                'url' => 'mysql://foo:secret_password@localhost/database_name',
            ],
        ];

        $config = new Config($data);
        $processor = new DatabaseUrlProcessor();
        $processor->process($config);

        $dbParams = $config->get('database');
        $this->assertSame('database_name', $dbParams['name']);
        $this->assertSame('localhost', $dbParams['host']);
        $this->assertSame('foo', $dbParams['user']);
        $this->assertSame('another_secret_password', $dbParams['password']);
        $this->assertArrayNotHasKey('port', $dbParams);
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
        $processor = new DatabaseUrlProcessor();
        $processor->process($config);

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
        $processor = new DatabaseUrlProcessor();

        $this->expectException(CompileException::class);
        $processor->process($config);
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
        $processor = new DatabaseUrlProcessor();

        $this->expectException(CompileException::class);
        $processor->process($config);
    }
}
