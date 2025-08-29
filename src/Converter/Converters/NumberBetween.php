<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Converters;

use Smile\GdprDump\Converter\Converter;
use Smile\GdprDump\Converter\Exception\InvalidParameterException;
use Smile\GdprDump\Converter\IsConfigurable;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;

final class NumberBetween implements Converter, IsConfigurable
{
    private int $min;
    private int $max;

    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('min', Parameter::TYPE_INT, true)
            ->addParameter('max', Parameter::TYPE_INT, true)
            ->process($parameters);

        $this->min = $input->get('min');
        $this->max = $input->get('max');

        if ($this->min > $this->max) {
            throw new InvalidParameterException('The parameter "min" must be lower than the parameter "max".');
        }
    }

    public function convert(mixed $value): int
    {
        return mt_rand($this->min, $this->max);
    }
}
