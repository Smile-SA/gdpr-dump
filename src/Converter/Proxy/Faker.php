<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Faker\FakerService;

class Faker implements ConverterInterface
{
    private object $provider;
    private string $method;
    private array $arguments;

    /**
     * @var int[]
     */
    private array $placeholders = [];

    public function __construct(private FakerService $fakerService)
    {
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

        // Create the formatter now to ensure that errors related to undefined formatters
        // are triggered before the start of the dump process
        $formatter = $input->get('formatter');
        // @phpstan-ignore-next-line getFormatter function always returns an array with 2 items
        [$this->provider, $this->method] = $this->fakerService
            ->getGenerator()
            ->getFormatter($formatter);

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
        $arguments = $this->arguments;

        // Replace all occurrences of "{{value}}" by $value
        foreach ($this->placeholders as $name) {
            $arguments[$name] = $value;
        }

        return $this->provider->{$this->method}(...$arguments);
    }
}
