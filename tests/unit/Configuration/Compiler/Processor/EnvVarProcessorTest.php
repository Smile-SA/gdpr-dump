<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Compiler\Processor;

use Smile\GdprDump\Configuration\Compiler\Processor\EnvVarProcessor;
use Smile\GdprDump\Configuration\Loader\Container;
use Smile\GdprDump\Configuration\Loader\Env\EnvVarParser;
use Smile\GdprDump\Tests\Unit\TestCase;

final class EnvVarProcessorTest extends TestCase
{
    /**
     * Assert that the processor uses the env var parser on the parsed data.
     */
    public function testProcessor(): void
    {
        putenv('TEST_VAR=1');

        $container = new Container((object) ['strict_schema' => '%env(bool:TEST_VAR)%']);
        $processor = new EnvVarProcessor(new EnvVarParser());
        $processor->process($container);
        $this->assertTrue($container->get('strict_schema'));

        putenv('TEST_VAR');
    }
}
