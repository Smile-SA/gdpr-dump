<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use Faker\Generator;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Faker\FakerService;

class Faker implements ConverterInterface
{
    private Generator $faker;
    private object $provider;
    private string $method;
    private array $arguments;

    /**
     * @var int[]
     */
    private array $placeholders = [];

    public function __construct(FakerService $faker)
    {
        $this->faker = $faker->getGenerator();
    }

    /**
     * @inheritdoc
     */
    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('formatter', Parameter::TYPE_STRING, true)
            ->addParameter('arguments', Parameter::TYPE_ARRAY, false, [])
            ->process($parameters);

        $formatter = $input->get('formatter');
        $this->arguments = $input->get('arguments') ?? [];

        foreach ($this->arguments as $name => $value) {
            if ($value === '{{value}}') {
                $this->placeholders[] = $name;
            }
        }

        // Faster than calling the "format" method of the Faker generator
        // (the "format" method uses call_user_func_array, which is very slow)
        // @phpstan-ignore-next-line getFormatter function always returns an array with 2 items
        [$this->provider, $this->method] = $this->faker->getFormatter($formatter);
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): mixed
    {
        $arguments = $this->arguments;

        // Replace all occurrences of "{{value}}" by $value
        foreach ($this->placeholders as $name) {
            $arguments[$name] = $value;
        }

        return $this->provider->{$this->method}(...$arguments);
    }
}
