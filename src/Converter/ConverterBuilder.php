<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use RuntimeException;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Dumper\Config\Definition\ConverterConfig;
use Smile\GdprDump\Dumper\DumpContext;
use UnexpectedValueException;

final class ConverterBuilder
{
    private DumpContext $dumpContext;

    public function __construct(private ConverterFactory $converterFactory)
    {
    }

    /**
     * Build a converter from a definition array.
     */
    public function build(ConverterConfig $definition): ConverterInterface
    {
        $name = $definition->getName();
        $parameters = $this->parseParameters($definition);

        // Create the converter
        $converter = $this->createConverter($name, $parameters);

        // Disallow using internal converters
        if ($converter instanceof InternalConverterInterface) {
            throw new UnexpectedValueException(
                sprintf('The converter "%s" is an internal implementation.', $name)
            );
        }

        // Add unique/cache/conditional converters if specified in the definition
        $converter = $this->bindUnique($converter, $definition);
        $converter = $this->bindCache($converter, $definition);

        return $this->bindCondition($converter, $definition);
    }

    /**
     * Set the dump context.
     */
    public function setDumpContext(DumpContext $dumpContext): void
    {
        $this->dumpContext = $dumpContext;
    }

    /**
     * If the "unique" parameter is set to true, bind a unique converter to the specified converter.
     */
    private function bindUnique(ConverterInterface $converter, ConverterConfig $definition): ConverterInterface
    {
        if ($definition->isUnique()) {
            $converter = $this->createConverter('unique', ['converter' => $converter]);
        }

        return $converter;
    }

    /**
     * If a cache key is defined, bind a cache converter to the specified converter.
     */
    private function bindCache(ConverterInterface $converter, ConverterConfig $definition): ConverterInterface
    {
        if ($definition->getCacheKey() !== '') {
            $converter = $this->createConverter(
                'cache',
                ['converter' => $converter, 'cache_key' => $definition->getCacheKey()]
            );
        }

        return $converter;
    }

    /**
     * If a condition is defined, bind a condition converter to the specified converter.
     */
    private function bindCondition(ConverterInterface $converter, ConverterConfig $definition): ConverterInterface
    {
        // Convert data only if it matches the specified condition
        if ($definition->getCondition() !== '') {
            $converter = $this->createConverter(
                'conditional',
                ['converter' => $converter, 'condition' => $definition->getCondition()]
            );
        }

        return $converter;
    }

    /**
     * Parse the converter parameters.
     *
     * @throws UnexpectedValueException
     */
    private function parseParameters(ConverterConfig $definition): array
    {
        $parameters = $definition->getParameters();

        foreach ($parameters as $name => $value) {
            if ($name === 'converters' || str_contains($name, '_converters')) {
                // Param is an array of converter definitions (e.g. "converters" param of the "chain" converter)
                $parameters[$name] = $this->parseConvertersParameter($name, $value);
                continue;
            }

            if ($name === 'converter' || str_contains($name, '_converter')) {
                // Param is a converter definition (e.g. "converter" param of the "unique" converter)
                $parameters[$name] = $this->parseConverterParameter($name, $value);
            }
        }

        return $parameters;
    }

    /**
     * Parse a parameter that defines an array of converter definitions.
     *
     * @return ConverterInterface[]
     * @throws UnexpectedValueException
     */
    private function parseConvertersParameter(string $name, mixed $definitionsCandidate): array
    {
        if (!is_array($definitionsCandidate)) {
            throw new UnexpectedValueException(sprintf('The parameter "%s" must be an array.', $name));
        }

        foreach ($definitionsCandidate as $index => $definitionCandidate) {
            $candidateName = $name . '[' . $index . ']';
            $definitionsCandidate[$index] = $this->parseConverterParameter($candidateName, $definitionCandidate);
        }

        return $definitionsCandidate;
    }

    /**
     * Parse a parameter that defines a converter definition.
     *
     * @throws UnexpectedValueException
     */
    private function parseConverterParameter(string $name, mixed $definitionCandidate): ConverterInterface
    {
        if (!is_array($definitionCandidate)) {
            throw new UnexpectedValueException(sprintf('The parameter "%s" must be an array.', $name));
        }

        return $this->build(new ConverterConfig($definitionCandidate));
    }

    /**
     * Create a converter that matches the specified name and parameters.
     */
    private function createConverter(string $name, array $parameters): ConverterInterface
    {
        $converter = $this->converterFactory->create($name);

        try {
            $converter->setParameters($parameters);
        } catch (ValidationException $e) {
            throw new RuntimeException(
                sprintf('An error occurred while parsing the converter "%s": %s', $name, lcfirst($e->getMessage()))
            );
        }

        if ($converter instanceof ContextAwareInterface) {
            if (!isset($this->dumpContext)) {
                throw new RuntimeException('The dump context is not set.');
            }

            $converter->setDumpContext($this->dumpContext);
        }

        return $converter;
    }
}
