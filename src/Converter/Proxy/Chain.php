<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;

class Chain implements ConverterInterface
{
    /**
     * @var ConverterInterface[]
     */
    private array $converters;

    /**
     * @inheritdoc
     */
    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('converters', Parameter::TYPE_ARRAY, true)
            ->process($parameters);

        $this->converters = $input->get('converters');
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): mixed
    {
        foreach ($this->converters as $converter) {
            $value = $converter->convert($value, $context);
        }

        return $value;
    }
}
