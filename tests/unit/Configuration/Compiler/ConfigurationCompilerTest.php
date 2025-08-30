<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Compiler;

use Smile\GdprDump\Configuration\Compiler\CompilerStep;
use Smile\GdprDump\Configuration\Compiler\ConfigurationCompiler;
use Smile\GdprDump\Configuration\Compiler\Processor\Processor;
use Smile\GdprDump\Configuration\Exception\JsonSchemaException;
use Smile\GdprDump\Configuration\Loader\Container;
use Smile\GdprDump\Configuration\Validator\JsonSchemaValidator;
use Smile\GdprDump\Tests\Unit\TestCase;

final class ConfigurationCompilerTest extends TestCase
{
    /**
     * Test the configuration compiler.
     */
    public function testCompiler(): void
    {
        $container = new Container((object) ['to_remove' => true]);
        $this->createCompiler()->compile($container);
        $this->assertTrue($container->has('key'));
        $this->assertSame('value', $container->get('key'));
        $this->assertFalse($container->has('to_remove'));
    }

    /**
     * Assert that the compiler uses the schema validator.
     */
    public function testCompilerUsesSchemaValidator(): void
    {
        $container = new Container((object) ['invalid' => true]);
        $this->expectException(JsonSchemaException::class);
        $this->createCompiler()->compile($container);
    }

    /**
     * Create a compiler object.
     */
    private function createCompiler(): ConfigurationCompiler
    {
        $processors = [
            new class implements Processor {
                public function getStep(): CompilerStep
                {
                    return CompilerStep::BEFORE_VALIDATION;
                }

                public function process(Container $container): void
                {
                    $container->remove('to_remove');
                }
            },
            new class implements Processor {
                public function getStep(): CompilerStep
                {
                    return CompilerStep::AFTER_VALIDATION;
                }

                public function process(Container $container): void
                {
                    $container->set('key', 'value');
                }
            },
        ];

        return new ConfigurationCompiler(
            new JsonSchemaValidator($this->getBasePath() . '/app/config/schema.json'),
            $processors
        );
    }
}
