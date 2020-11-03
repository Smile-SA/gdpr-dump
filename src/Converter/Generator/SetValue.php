<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Generator;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

class SetValue implements ConverterInterface
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param array $parameters
     * @throws ValidationException
     */
    public function __construct(array $parameters)
    {
        $input = (new ParameterProcessor())
            ->addParameter('value', null, true)
            ->process($parameters);

        $this->value = $input->get('value');
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return $this->value;
    }
}
