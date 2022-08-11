<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Generator;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

class NumberBetween implements ConverterInterface
{
    private int $min;
    private int $max;

    /**
     * @throws ValidationException
     */
    public function __construct(array $parameters = [])
    {
        $input = (new ParameterProcessor())
            ->addParameter('min', Parameter::TYPE_INT, true)
            ->addParameter('max', Parameter::TYPE_INT, true)
            ->process($parameters);

        $this->min = $input->get('min');
        $this->max = $input->get('max');

        if ($this->min > $this->max) {
            throw new ValidationException('The parameter "min" must be lower than the parameter "max".');
        }
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): mixed
    {
        return mt_rand($this->min, $this->max);
    }
}
