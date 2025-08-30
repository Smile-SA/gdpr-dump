<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Compiler;

use Smile\GdprDump\Configuration\Compiler\ConfigurationCompiler;
use Smile\GdprDump\Configuration\Compiler\Processor\Processor;
use Smile\GdprDump\Configuration\Compiler\ProcessorType;
use Smile\GdprDump\Configuration\Loader\Container;
use Smile\GdprDump\Configuration\Validator\JsonSchemaValidator;
use Smile\GdprDump\Tests\Unit\TestCase;

final class ConfigurationCompilerTest extends TestCase
{
    /**
     * Assert that converter templates are merged into the configuration.
     */
    public function testCompilerr(): void
    {
        $container = new Container((object) ['to_remove' => true]);
        $this->createCompiler()->compile($container);
        $this->assertTrue($container->has('key'));
        $this->assertSame('value', $container->get('key'));
        $this->assertFalse($container->has('to_remove'));
    }

    /**
     * Create a compiler object.
     */
    private function createCompiler(): ConfigurationCompiler
    {
        $processors = [
            new class implements Processor {
                public function getType(): ProcessorType
                {
                    return ProcessorType::BEFORE_VALIDATION;
                }

                public function process(Container $container): void
                {
                    $container->remove('to_remove');
                }
            },
            new class implements Processor {
                public function getType(): ProcessorType
                {
                    return ProcessorType::AFTER_VALIDATION;
                }

                public function process(Container $container): void
                {
                    $container->set('key', 'value');
                }
            },
        ];

        return new ConfigurationCompiler(
            new JsonSchemaValidator($this->getResource('test_schema.json')),
            $processors
        );
    }
}
