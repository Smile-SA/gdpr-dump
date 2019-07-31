<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use Faker\Generator;
use InvalidArgumentException;

class Faker implements ConverterInterface
{
    /**
     * @var Generator
     */
    protected $faker;

    /**
     * @var string
     */
    protected $formatter;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var int[]
     */
    private $placeholders = [];

    /**
     * @param array $parameters
     * @throws InvalidArgumentException
     */
    public function __construct(array $parameters)
    {
        if (empty($parameters['faker'])) {
            throw new InvalidArgumentException('The Faker converter requires the "faker" parameter.');
        }

        if (empty($parameters['formatter'])) {
            throw new InvalidArgumentException('The Faker converter requires the "formatter" parameter.');
        }

        $parameters += [
            'arguments' => [],
        ];

        $this->faker = $parameters['faker'];
        $this->formatter = $parameters['formatter'];
        $this->arguments = $parameters['arguments'];

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
