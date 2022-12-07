<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use RuntimeException;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Proxy\Cache;
use Smile\GdprDump\Converter\Proxy\Conditional;
use Smile\GdprDump\Converter\Proxy\Faker;
use Smile\GdprDump\Converter\Proxy\Unique;
use Smile\GdprDump\Faker\FakerService;
use UnexpectedValueException;

class ConverterFactory
{
    public function __construct(private ConverterResolver $converterResolver, private FakerService $faker)
    {
    }

    /**
     * Create a converter from a definition array.
     *
     * @throws ValidationException
     */
    public function create(array $definition): ConverterInterface
    {
        $definition = $this->getConverterData($definition);

        // Get the converter name and parameters
        $name = $definition['converter'];
        $parameters = $definition['parameters'];

        // Create the converter
        $converter = $this->createConverter($name, $parameters);

        // Generate only unique values
        if ($definition['unique']) {
            $converter = new Unique(['converter' => $converter]);
        }

        if ($definition['cache_key'] !== '') {
            $converter = new Cache(['converter' => $converter, 'cache_key' => $definition['cache_key']]);
        }

        // Convert data only if it matches the specified condition
        if ($definition['condition'] !== '') {
            $converter = new Conditional([
                'condition' => $definition['condition'],
                'if_true_converter' => $converter,
            ]);
        }

        return $converter;
    }

    /**
     * Get the converter data.
     *
     * @throws UnexpectedValueException
     */
    private function getConverterData(array $definition): array
    {
        if (!array_key_exists('converter', $definition)) {
            throw new UnexpectedValueException('The converter name is required.');
        }

        if (array_key_exists('parameters', $definition) && !is_array($definition['parameters'])) {
            throw new UnexpectedValueException('The converter parameters must be an array.');
        }

        $definition['converter'] = (string) $definition['converter'];
        if ($definition['converter'] === '') {
            throw new UnexpectedValueException('The converter name is required.');
        }

        $definition += [
            'parameters' => [],
            'condition' => '',
            'cache_key' => '',
            'unique' => false,
        ];

        // Parse the parameters
        $definition['parameters'] = $this->parseParameters($definition['parameters']);

        // Cast values
        $definition['condition'] = (string) $definition['condition'];
        $definition['unique'] = (bool) $definition['unique'];
        $definition['cache_key'] = (string) $definition['cache_key'];

        return $definition;
    }

    /**
     * Parse the converter parameters.
     *
     * @throws UnexpectedValueException
     */
    private function parseParameters(array $parameters): array
    {
        foreach ($parameters as $name => $value) {
            if ($name === 'converters' || str_contains($name, '_converters')) {
                // Param is an array of converter definitions (e.g. "converters" param of the "chain" converter)
                $parameters[$name] = $this->parseConvertersParameter($name, $value);
                continue;
            }

            if ($name === 'converter' || str_contains($name, '_converter')) {
                // Param is a converter definition (e.g. "converter" param of the "unique" converter
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
    private function parseConvertersParameter(string $name, mixed $parameter): array
    {
        if (!is_array($parameter)) {
            throw new UnexpectedValueException(sprintf('The parameter "%s" must be an array.', $name));
        }

        foreach ($parameter as $index => $definition) {
            $parameter[$index] = $this->parseConverterParameter($name . '[' . $index . ']', $definition);
        }

        return $parameter;
    }

    /**
     * Parse a parameter that defines a converter definition.
     *
     * @throws UnexpectedValueException
     */
    private function parseConverterParameter(string $name, mixed $parameter): ConverterInterface
    {
        if (!is_array($parameter)) {
            throw new UnexpectedValueException(sprintf('The parameter "%s" must be an array.', $name));
        }

        return $this->create($parameter);
    }

    /**
     * Create a converter object from its name and parameters.
     *
     * @throws RuntimeException
     */
    private function createConverter(string $name, array $parameters = []): ConverterInterface
    {
        $className = $this->converterResolver->getClassName($name);

        // Faker parameter
        if (($className === Faker::class || is_subclass_of($className, Faker::class)) && !isset($parameters['faker'])) {
            $parameters['faker'] = $this->faker->getGenerator();
        }

        $converter = new $className($parameters);
        if (!$converter instanceof ConverterInterface) {
            throw new RuntimeException(
                sprintf('The class "%s" does not implement Smile\\GdprDump\\Converter\\ConverterInterface.', $className)
            );
        }

        return $converter;
    }
}
