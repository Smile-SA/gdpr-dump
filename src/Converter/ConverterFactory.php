<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Smile\GdprDump\Converter\Proxy\Cache;
use Smile\GdprDump\Converter\Proxy\Conditional;
use Smile\GdprDump\Converter\Proxy\Unique;
use Smile\GdprDump\Faker\FakerService;
use UnexpectedValueException;

class ConverterFactory
{
    /**
     * @var FakerService
     */
    private $faker;

    /**
     * e.g. ['unique' => 'Smile\GdprDump\Converter\Proxy\Unique', ...]
     *
     * @var string[]
     */
    private $classNames;

    /**
     * @param FakerService $faker
     */
    public function __construct(FakerService $faker)
    {
        $this->faker = $faker;
    }

    /**
     * Create a converter from a definition.
     * It can be either a string that represents the converter name,
     * or an array that represents the converter data.
     *
     * @param array $definition
     * @return ConverterInterface
     */
    public function create(array $definition): ConverterInterface
    {
        $definition = $this->getConverterData($definition);

        // If converter is disabled, return a dummy converter
        if ($definition['disabled']) {
            return new Dummy();
        }

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
     * @param array $definition
     * @return array
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
            'disabled' => false,
        ];

        // Parse the parameters
        $definition['parameters'] = $this->parseParameters($definition['parameters']);

        // Cast values
        $definition['condition'] = (string) $definition['condition'];
        $definition['unique'] = (bool) $definition['unique'];
        $definition['cache_key'] = (string) $definition['cache_key'];
        $definition['disabled'] = (bool) $definition['disabled'];

        return $definition;
    }

    /**
     * Parse the converter parameters.
     *
     * @param array $parameters
     * @return array
     * @throws UnexpectedValueException
     */
    private function parseParameters(array $parameters): array
    {
        foreach ($parameters as $name => $value) {
            if ($name === 'converters' || strpos($name, '_converters') !== false) {
                // Param is an array of converter definitions (e.g. "converters" param of the "chain" converter)
                $parameters[$name] = $this->parseConvertersParameter($name, $value);
                continue;
            }

            if ($name === 'converter' || strpos($name, '_converter') !== false) {
                // Param is a converter definition (e.g. "converter" param of the "unique" converter
                $parameters[$name] = $this->parseConverterParameter($name, $value);
            }
        }

        return $parameters;
    }

    /**
     * Parse a parameter that defines an array of converter definitions.
     *
     * @param string $name
     * @param mixed $parameter
     * @return ConverterInterface[]
     * @throws UnexpectedValueException
     */
    private function parseConvertersParameter(string $name, $parameter): array
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
     * @param string $name
     * @param mixed $parameter
     * @return ConverterInterface
     * @throws UnexpectedValueException
     */
    private function parseConverterParameter(string $name, $parameter): ConverterInterface
    {
        if (!is_array($parameter)) {
            throw new UnexpectedValueException(sprintf('The parameter "%s" must be an array.', $name));
        }

        return $this->create($parameter);
    }

    /**
     * Create a converter object from its name and parameters.
     *
     * @param string $name
     * @param array $parameters
     * @return ConverterInterface
     * @throws RuntimeException
     */
    private function createConverter(string $name, array $parameters = []): ConverterInterface
    {
        $className = $name;

        if (strpos($name, '\\') === false) {
            // Find class names of default converters
            $this->initClassNames();

            // Check if the converter is a class declared in this namespace
            if (array_key_exists($name, $this->classNames)) {
                $className = $this->classNames[$name];
            }
        }

        if (!class_exists($className)) {
            throw new RuntimeException(sprintf('The converter class "%s" was not found.', $className));
        }

        // Faker parameter
        if (($className === Faker::class || is_subclass_of($className, Faker::class)) && !isset($parameters['faker'])) {
            $parameters['faker'] = $this->faker->getGenerator();
        }

        return new $className($parameters);
    }

    /**
     * Initialize the converter name <-> class name array.
     */
    private function initClassNames()
    {
        if ($this->classNames === null) {
            $this->classNames = $this->findClassNames(__DIR__);
        }
    }

    /**
     * Get converter class names that reside in the specified directory.
     * e.g. ['unique' => 'Smile\GdprDump\Data\Converter\Proxy\Unique', ...]
     *
     * @param string $directory
     * @param string $baseDirectory
     * @return array
     * @throws ReflectionException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function findClassNames(string $directory, string $baseDirectory = ''): array
    {
        $result = [];
        $files = scandir($directory);

        foreach ($files as $fileName) {
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }

            // Absolute path of the file
            $path = $directory . '/' . $fileName;

            if (is_dir($path)) {
                // Recursively find files in this directory
                $newBaseDirectory = $baseDirectory ? $baseDirectory . '/' . $fileName : $fileName;
                $result = array_merge($result, $this->findClassNames($path, $newBaseDirectory));
            } else {
                // Remove the extension
                $fileName = pathinfo($fileName, PATHINFO_FILENAME);

                // Get the class name
                $className = 'Smile\GdprDump\Converter\\';
                $className .= $baseDirectory ? str_replace('/', '\\', $baseDirectory) . '\\' . $fileName : $fileName;

                // Include only classes that implement the converter interface
                $reflection = new ReflectionClass($className);

                if ($reflection->isSubclassOf(ConverterInterface::class)) {
                    $result[lcfirst($fileName)] = $className;
                }
            }
        }

        return $result;
    }
}
