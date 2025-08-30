<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Compiler;

use Smile\GdprDump\Configuration\Compiler\Processor\Processor;
use Smile\GdprDump\Configuration\Exception\ConfigurationException;
use Smile\GdprDump\Configuration\Loader\Container;
use Smile\GdprDump\Configuration\Validator\JsonSchemaValidator;

final class ConfigurationCompiler
{
    /**
     * @param array<string, Processor[]> $processors
     */
    private array $processors = [];

    public function __construct(private JsonSchemaValidator $schemaValidator, iterable $processors)
    {
        foreach ($processors as $processors) {
            $this->addProcessor($processors);
        }
    }

    /**
     * Compile the configuration.
     *
     * @throws ConfigurationException
     */
    public function compile(Container $container): void
    {
        // Actions that must be performed before the validation
        $this->runProcessors($container, CompilerStep::BEFORE_VALIDATION);

        // Validate the configuration against a JSON schema
        $this->schemaValidator->validate($container->getRoot());

        // Actions that must be performed after the validation
        $this->runProcessors($container, CompilerStep::AFTER_VALIDATION);
    }

    /**
     * Add a processor to the compiler.
     */
    private function addProcessor(Processor $processor): self
    {
        $step = $processor->getStep()->name;
        if (!array_key_exists($step, $this->processors)) {
            $this->processors[$step] = [];
        }

        $this->processors[$step][] = $processor;

        return $this;
    }

    /**
     * Run processors that match the specified type.
     */
    private function runProcessors(Container $container, CompilerStep $step): void
    {
        foreach ($this->processors[$step->name] ?? [] as $processor) {
            $processor->process($container);
        }
    }
}
