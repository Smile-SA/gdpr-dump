<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Converter;

use Smile\Anonymizer\Converter\Proxy\Conditional;
use Smile\Anonymizer\Converter\Proxy\Unique;
use Smile\Anonymizer\Faker\FakerService;

class ConverterFactory
{
    /**
     * @var FakerService
     */
    private $faker;

    /**
     * e.g. ['unique' => 'Smile\Anonymizer\Data\Converter\Proxy\Unique', ...]
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
     * @param string|array $definition
     * @return ConverterInterface
     * @throws \UnexpectedValueException
     */
    public function create($definition): ConverterInterface
    {
        $definition =  $this->getConverterData($definition);

        // Get the converter name and parameters
        $name = $definition['converter'];
        $parameters = $definition['parameters'];

        // Create the converter
        $converter =  $this->createConverter($name, $parameters);

        // Generate only unique values
        if ($definition['unique']) {
            $converter = new Unique(['converter' => $converter]);
        }

        // Convert data only if it matches the specified condition
        if ($definition['condition']) {
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
     * @param string|array $definition
     * @return array
     * @throws \UnexpectedValueException
     */
    private function getConverterData($definition)
    {
        if (!is_array($definition)) {
            $definition = ['converter' => $definition];
        }

        if (!array_key_exists('converter', $definition)) {
            throw new \UnexpectedValueException('The converter name is required.');
        }

        if (array_key_exists('parameters', $definition) && !is_array($definition['parameters'])) {
            throw new \UnexpectedValueException('The converter parameters must be an array.');
        }

        $definition['converter'] = (string) $definition['converter'];

        if ($definition['converter'] === '') {
            throw new \UnexpectedValueException('The converter name is required.');
        }

        $definition += [
            'parameters' => [],
            'condition' => '',
            'unique' => false,
            'optional' => false,
            'chain' => null,
        ];

        $definition['parameters'] =  $this->parseParameters($definition['parameters']);
        $definition['condition'] = (string) $definition['condition'];
        $definition['unique'] = (bool) $definition['unique'];
        $definition['optional'] = (bool) $definition['optional'];

        return $definition;
    }

    /**
     * Parse the converter parameters.
     *
     * @param array $parameters
     * @return array
     * @throws \UnexpectedValueException
     */
    private function parseParameters($parameters): array
    {
        foreach ($parameters as $name => $value) {
            // Some converters require other converters as a parameter (e.g. unique, chain...)
            if ($name === 'converters' || strpos($name, '_converters') !== false) {
                // Param is an array of converter definitions
                if (!is_array($value)) {
                    throw new \UnexpectedValueException('The "converters" parameter must be an array.');
                }

                foreach ($value as $k => $v) {
                    $value[$k] =  $this->create($v);
                }

                $parameters[$name] = $value;
            } elseif ($name === 'converter' || strpos($name, '_converter') !== false) {
                // Param is a converter definition
                $parameters[$name] =  $this->create($value);
            }
        }

        return $parameters;
    }

    /**
     * Get the class name of a converter.
     *
     * @param string $name
     * @param array $parameters
     * @return ConverterInterface
     */
    private function createConverter(string $name, array $parameters = []): ConverterInterface
    {
        $className = $name;

        if (strpos($name, '\\') === false) {
            // Find class names of default converters
            $this->initClassNames();

            // Check if the converter is a class declared in this namespace
            if (array_key_exists($name,  $this->classNames)) {
                $className = $this->classNames[$name];
            }
        }

        // If no class was found, check if a Faker formatter has this name
        if (!class_exists($className)) {
            try {
                $this->faker->getGenerator()->getFormatter($name);
                $className = Faker::class;
                $parameters['formatter'] = $name;
            } catch (\InvalidArgumentException $e) {
                throw new \RuntimeException(sprintf('The converter class "%s" was not found.', $className));
            }
        }

        // Faker parameter
        if (($className === Faker::class || is_subclass_of($className, Faker::class)) && !isset($parameters['faker'])) {
            $parameters['faker'] =  $this->faker->getGenerator();
        }

        return new $className($parameters);
    }

    /**
     * Initialize the converter name <-> class name array.
     */
    private function initClassNames()
    {
        if ( $this->classNames !== null) {
            return  $this->classNames;
        }

         $this->classNames =  $this->findClassNames(__DIR__);
    }

    /**
     * Get converter class names that reside in the specified directory.
     * e.g. ['unique' => 'Smile\Anonymizer\Data\Converter\Proxy\Unique', ...]
     *
     * @param string $directory
     * @param string $baseDirectory
     * @return array
     * @throws \ReflectionException
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
                $result = array_merge($result,  $this->findClassNames($path, $fileName));
            } else {
                // Remove the extension
                $fileName = pathinfo($fileName, PATHINFO_FILENAME);

                // Get the class name
                $className = 'Smile\Anonymizer\Converter\\';
                $className .= $baseDirectory ? $baseDirectory . '\\' . $fileName : $fileName;

                // Include only classes that implement the converter interface
                $reflection = new \ReflectionClass($className);

                if ($reflection->isSubclassOf(ConverterInterface::class)) {
                    $result[lcfirst($fileName)] = $className;
                }
            }
        }

        return $result;
    }
}
