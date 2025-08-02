<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Transformer;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;

final class AppendText implements ConverterInterface
{
    private string $suffix;

    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('value', Parameter::TYPE_STRING, true)
            ->process($parameters);

        $this->suffix = $input->get('value');
    }

    public function convert(mixed $value): string
    {
        $value = (string) $value;

        return $value !== '' ? $value . $this->suffix : $value;
    }
}
