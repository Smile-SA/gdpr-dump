<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Util\ArrayHelper;

class FromContext implements ConverterInterface
{
    private string $key;

    /**
     * @throws ValidationException
     */
    public function __construct(array $parameters = [])
    {
        $input = (new ParameterProcessor())
            ->addParameter('key', Parameter::TYPE_STRING, true)
            ->process($parameters);

        $this->key = $input->get('key');
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): mixed
    {
        return ArrayHelper::getPath($context, $this->key);
    }
}
