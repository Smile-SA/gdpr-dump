<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use Faker\Generator;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

class Faker implements ConverterInterface
{
    private Generator $faker;
    private string $formatter;
    private array $arguments;

    /**
     * @var int[]
     */
    private array $placeholders = [];

    /**
     * @throws ValidationException
     */
    public function __construct(array $parameters)
    {
        $input = (new ParameterProcessor())
            ->addParameter('faker', Generator::class, true)
            ->addParameter('formatter', Parameter::TYPE_STRING, true)
            ->addParameter('arguments', Parameter::TYPE_ARRAY, false, [])
            ->process($parameters);

        $this->faker = $input->get('faker');
        $this->formatter = $input->get('formatter');
        $this->arguments = $input->get('arguments') ?? [];

        foreach ($this->arguments as $name => $value) {
            if ($value === '{{value}}') {
                $this->placeholders[] = $name;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): mixed
    {
        // Faster than calling the "format" method of the Faker generator
        // (the "format" method uses call_user_func_array, which is very slow)
        // @phpstan-ignore-next-line getFormatter function always returns an array with 2 items
        [$provider, $method] = $this->faker->getFormatter($this->formatter);

        $arguments = $this->arguments;

        // Replace all occurrences of "{{value}}" by $value
        foreach ($this->placeholders as $name) {
            $arguments[$name] = $value;
        }

        return $provider->$method(...$arguments);
    }
}
