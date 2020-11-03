<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Transformer;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

class AddSuffix implements ConverterInterface
{
    /**
     * @var string
     */
    private $suffix;

    /**
     * @param array $parameters
     * @throws ValidationException
     */
    public function __construct(array $parameters = [])
    {
        $input = (new ParameterProcessor())
            ->addParameter('suffix', Parameter::TYPE_STRING, true)
            ->process($parameters);

        $this->suffix = $input->get('suffix');
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $value = (string) $value;

        return $value !== '' ? $value . $this->suffix : $value;
    }
}
