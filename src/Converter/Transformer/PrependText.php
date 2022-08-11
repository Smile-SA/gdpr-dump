<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Transformer;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

class PrependText implements ConverterInterface
{
    private string $prefix;

    /**
     * @throws ValidationException
     */
    public function __construct(array $parameters = [])
    {
        $input = (new ParameterProcessor())
            ->addParameter('value', Parameter::TYPE_STRING, true)
            ->process($parameters);

        $this->prefix = $input->get('value');
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): mixed
    {
        $value = (string) $value;

        return $value !== '' ? $this->prefix . $value : $value;
    }
}
