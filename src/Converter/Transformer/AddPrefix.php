<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Transformer;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

class AddPrefix implements ConverterInterface
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @param array $parameters
     * @throws ValidationException
     */
    public function __construct(array $parameters = [])
    {
        $input = (new ParameterProcessor())
            ->addParameter('prefix', Parameter::TYPE_STRING, true)
            ->process($parameters);

        $this->prefix = $input->get('prefix');
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $value = (string) $value;

        return $value !== '' ? $this->prefix . $value : $value;
    }
}
