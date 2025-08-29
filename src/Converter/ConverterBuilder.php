<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use Smile\GdprDump\Configuration\Definition\ConverterConfig;
use Smile\GdprDump\Converter\Converter;
use Smile\GdprDump\Converter\ConverterFactory;
use Smile\GdprDump\Converter\Exception\BuildException;
use Smile\GdprDump\Converter\Exception\InternalConverterException;
use Smile\GdprDump\Converter\Exception\InvalidParameterException;
use Smile\GdprDump\Converter\IsConfigurable;
use Smile\GdprDump\Converter\IsContextAware;
use Smile\GdprDump\Converter\IsFakerAware;
use Smile\GdprDump\Converter\IsInternal;
use Smile\GdprDump\Dumper\DumpContext;
use Smile\GdprDump\Faker\LazyGenerator;

final class ConverterBuilder
{
    public function __construct(
        private ConverterFactory $converterFactory,
        private DumpContext $dumpContext,
        private LazyGenerator $lazyFaker,
    ) {
    }

    /**
     * Build a converter from a definition array.
     *
     * @throws BuildException
     */
    public function build(ConverterConfig $definition): Converter
    {
        $name = $definition->getName();
        $parameters = $this->parseParameters($definition);

        // Create the converter
        $converter = $this->createConverter($name, $parameters);

        // Disallow using internal converters
        if ($converter instanceof IsInternal) {
            $message = 'The converter "%s" is an internal implementation. %s';
            throw new InternalConverterException(sprintf($message, $name, $converter->getAlternative()));
        }

        // Add unique/cache/conditional converters if specified in the definition
        $converter = $this->bindUnique($converter, $definition);
        $converter = $this->bindCache($converter, $definition);
        $converter = $this->bindCondition($converter, $definition);

        return $converter;
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
     */
    private function parseParameters(ConverterConfig $definition): array
    {
        $parameters = $definition->getParameters();

        foreach ($parameters as $name => $value) {
            if ($name === 'converters') {
                // Param is an array of converter definitions (e.g. "converters" param of the "chain" converter)
                $parameters[$name] = $this->parseConvertersParameter($name, $value);
                continue;
            }

            if ($name === 'converter') {
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
     */
    private function parseConvertersParameter(string $name, mixed $definitionsCandidate): array
    {
        if (!is_array($definitionsCandidate)) {
            throw new InvalidParameterException(sprintf('The parameter "%s" must be an array.', $name));
        }

        foreach ($definitionsCandidate as $index => $definitionCandidate) {
            $candidateName = $name . '[' . $index . ']';
            $definitionsCandidate[$index] = $this->parseConverterParameter($candidateName, $definitionCandidate);
        }

        return $definitionsCandidate;
    }

    /**
     * Parse a parameter that defines a converter definition.
     */
    private function parseConverterParameter(string $name, mixed $definitionCandidate): Converter
    {
        if (!$definitionCandidate instanceof ConverterConfig) {
            throw new InvalidParameterException(sprintf('The parameter "%s" must be a converter.', $name));
        }

        return $this->build($definitionCandidate);
    }

    /**
     * Create a converter that matches the specified name and parameters.
     */
    private function createConverter(string $name, array $parameters): Converter
    {
        $converter = $this->converterFactory->create($name);

        if ($converter instanceof IsContextAware) {
            $converter->setDumpContext($this->dumpContext);
        }

        if ($converter instanceof IsFakerAware) {
            $converter->setFaker($this->lazyFaker->getGenerator());
        }

        if ($converter instanceof IsConfigurable) {
            $converter->setParameters($parameters);
        }

        return $converter;
    }
}
