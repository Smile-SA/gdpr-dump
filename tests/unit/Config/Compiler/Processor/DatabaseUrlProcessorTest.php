<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Compiler\Processor;

use Smile\GdprDump\Config\Compiler\Processor\DatabaseUrlProcessor;
use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Tests\Unit\TestCase;

class DatabaseUrlProcessorTest extends TestCase
{
    /**
     * Assert that database URL is processed and parts (i.e. password) take precedence
     */
    public function testEnvVarProcessor(): void
    {
        $data = [
            'database' =>
                [
                    'password' => 'another_secret_password',
                    'url' => 'mysql://foo:secret_password@localhost/databasename',
                ],
        ];

        $config = new Config($data);
        $processor = new DatabaseUrlProcessor();
        $processor->process($config);

        $this->assertSame('localhost', $config->get('database')['host']);
        $this->assertSame('another_secret_password', $config->get('database')['password']);
    }
}
