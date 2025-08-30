<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Compiler;

use Smile\GdprDump\Configuration\Compiler\Processor\Processor;
use Smile\GdprDump\Configuration\Exception\ConfigurationException;
use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Configuration\Loader\Container;
use Smile\GdprDump\Configuration\Validator\JsonSchemaValidator;
use Throwable;

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
        try {
            // Actions that must be performed before the validation (e.g. resolving env vars)
            $this->runProcessors($container, ProcessorType::BEFORE_VALIDATION);

            // Validate the configuration against a JSON schema
            $this->schemaValidator->validate($container->getRoot());

            // Actions that must be performed after the validation (e.g. resolving virtual converters)
            $this->runProcessors($container, ProcessorType::AFTER_VALIDATION);
        } catch (Throwable $e) {
            throw $e instanceof ConfigurationException ? $e : new ParseException($e->getMessage(), $e);
        }
    }

    /**
     * Add a processor to the compiler.
     */
    private function addProcessor(Processor $processor): self
    {
        $type = $processor->getType()->name;
        if (!array_key_exists($type, $this->processors)) {
            $this->processors[$type] = [];
        }

        $this->processors[$type][] = $processor;

        return $this;
    }

    /**
     * Run processors that match the specified type.
     */
    private function runProcessors(Container $container, ProcessorType $type): void
    {
        foreach ($this->processors[$type->name] ?? [] as $processor) {
            $processor->process($container);
        }
    }
}
