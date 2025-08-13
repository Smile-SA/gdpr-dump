<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Converters;

use Smile\GdprDump\Converter\Converter;
use Smile\GdprDump\Converter\Exception\InvalidParameterException;
use Smile\GdprDump\Converter\IsConfigurable;
use Smile\GdprDump\Converter\IsFakerAware;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Traits\HasFaker;
use Throwable;

final class Faker implements Converter, IsConfigurable, IsFakerAware
{
    use HasFaker;

    private object $provider;
    private string $method;
    private array $arguments;

    /**
     * @var int[]
     */
    private array $placeholders = [];

    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('formatter', Parameter::TYPE_STRING, true)
            ->addParameter('arguments', Parameter::TYPE_ARRAY, false, [])
            ->process($parameters);

        $formatter = $input->get('formatter');
        try {
            // @phpstan-ignore-next-line getFormatter function always returns an array with 2 items
            [$this->provider, $this->method] = $this->faker->getFormatter($formatter);
        } catch (Throwable $e) {
            throw new InvalidParameterException(sprintf('Faker formatter error: %s', $e->getMessage()), $e);
        }

        $this->arguments = $input->get('arguments') ?? [];

        // Detect value placeholders
        foreach ($this->arguments as $name => $value) {
            if ($value === '{{value}}') {
                $this->placeholders[] = $name;
            }
        }
    }

    public function convert(mixed $value): mixed
    {
        $arguments = $this->arguments;

        // Replace all occurrences of "{{value}}" with $value
        foreach ($this->placeholders as $name) {
            $arguments[$name] = $value;
        }

        return $this->provider->{$this->method}(...$arguments);
    }
}
