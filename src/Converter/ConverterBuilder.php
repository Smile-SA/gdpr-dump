<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use Smile\GdprDump\Config\Definition\ConverterConfig;
use Smile\GdprDump\Converter\Exception\ConverterBuildException;
use Smile\GdprDump\Converter\Exception\ConverterBuildFailedException;
use Smile\GdprDump\Dumper\DumpContext;
use Throwable;

final class ConverterBuilder
{
    private DumpContext $dumpContext;

    public function __construct(private ConverterFactory $converterFactory)
    {
    }

    /**
     * Build a converter from a definition array.
     *
     * @throws ConverterBuildFailedException
     */
    public function build(ConverterConfig $definition): Converter
    {
        try {
            $name = $definition->getName();
            $parameters = $this->parseParameters($definition);

            // Create the converter
            $converter = $this->createConverter($name, $parameters);

            // Disallow using internal converters
            if ($converter instanceof IsInternal) {
                throw new ConverterBuildException(sprintf('The converter "%s" is an internal implementation.', $name));
            }

            // Add unique/cache/conditional converters if specified in the definition
            $converter = $this->bindUnique($converter, $definition);
            $converter = $this->bindCache($converter, $definition);
            $converter = $this->bindCondition($converter, $definition);
        } catch (ConverterBuildException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new ConverterBuildException($e->getMessage(), $e);
        }

        return $converter;
    }

    /**
     * Set the dump context.
     */
    public function setDumpContext(DumpContext $dumpContext): self
    {
        $this->dumpContext = $dumpContext;

        return $this;
    }

    /**
     * If the "unique" parameter is set to true, bind a unique converter to the specified converter.
     */
    private function bindUnique(Converter $converter, ConverterConfig $definition): Converter
    {
        if ($definition->isUnique()) {
            $converter = $this->createConverter('unique', ['converter' => $converter]);
        }

        return $converter;
    }

    /**
     * If a cache key is defined, bind a cache converter to the specified converter.
     */
    private function bindCache(Converter $converter, ConverterConfig $definition): Converter
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
    private function bindCondition(Converter $converter, ConverterConfig $definition): Converter
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
     * @throws ConverterBuildFailedException
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
     * @return Converter[]
     * @throws ConverterBuildFailedException
     */
    private function parseConvertersParameter(string $name, mixed $definitionsCandidate): array
    {
        if (!is_array($definitionsCandidate)) {
            throw new ConverterBuildException(sprintf('The parameter "%s" must be an array.', $name));
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
     * @throws ConverterBuildFailedException
     */
    private function parseConverterParameter(string $name, mixed $definitionCandidate): Converter
    {
        if (!is_array($definitionCandidate)) {
            throw new ConverterBuildException(sprintf('The parameter "%s" must be an array.', $name));
        }

        // TODO check if "converter" is defined?
        return $this->build((new ConverterConfig($definitionCandidate['converter']))->fromArray($definitionCandidate));
    }

    /**
     * Create a converter that matches the specified name and parameters.
     *
     * @throws ConverterBuildException
     */
    private function createConverter(string $name, array $parameters): Converter
    {
        $converter = $this->converterFactory->create($name);
        $converter->setParameters($parameters);

        if ($converter instanceof IsContextAware) {
            if (!isset($this->dumpContext)) {
                throw new ConverterBuildException('The dump context is not set.');
            }

            $converter->setDumpContext($this->dumpContext);
        }


        return $converter;
    }
}
