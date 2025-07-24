<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Compiler;

use Smile\GdprDump\Config\Compiler\Compiler;
use Smile\GdprDump\Config\Compiler\Processor\ProcessorInterface;
use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Tests\Unit\TestCase;

final class CompilerTest extends TestCase
{
    /**
     * Test the config compiler.
     */
    public function testCompiler(): void
    {
        $processor = new class implements ProcessorInterface {
            public function process(ConfigInterface $config): void
            {
                $counter = (int) $config->get('counter');
                $config->set('counter', ++$counter);
            }
        };

        $compiler = new Compiler([new $processor(), new $processor()]);
        $config = new Config();
        $compiler->compile($config);

        $this->assertTrue($config->has('counter'));
        $this->assertSame(2, $config->get('counter'));
    }
}
