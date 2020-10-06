<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use Faker\Generator;
use InvalidArgumentException;
use UnexpectedValueException;

class Faker implements ConverterInterface
{
    /**
     * @var Generator
     */
    private $faker;

    /**
     * @var string
     */
    private $formatter;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @var int[]
     */
    private $placeholders = [];

    /**
     * @param array $parameters
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function __construct(array $parameters)
    {
        if (!isset($parameters['faker'])) {
            throw new InvalidArgumentException('The parameter "faker" is required.');
        }

        if (!array_key_exists('formatter', $parameters)) {
            throw new InvalidArgumentException('The parameter "formatter" is required.');
        }

        if (array_key_exists('arguments', $parameters) && !is_array($parameters['arguments'])) {
            throw new UnexpectedValueException('The parameter "arguments" must be an array.');
        }

        $this->faker = $parameters['faker'];
        $this->formatter = (string) $parameters['formatter'];
        $this->arguments = $parameters['arguments'] ?? [];

        if ($this->formatter === '') {
            throw new UnexpectedValueException('The parameter "formatter" must not be empty.');
        }

        foreach ($this->arguments as $name => $value) {
            if ($value === '{{value}}') {
                $this->placeholders[] = $name;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        // Faster than calling the "format" method of the Faker generator
        // (the "format" method uses call_user_func_array, which is very slow)
        list($provider, $method) = $this->faker->getFormatter($this->formatter);

        $arguments = $this->arguments;

        // Replace all occurrences of "{{value}}" by $value
        foreach ($this->placeholders as $name) {
            $arguments[$name] = $value;
        }

        return $provider->$method(...$arguments);
    }
}
