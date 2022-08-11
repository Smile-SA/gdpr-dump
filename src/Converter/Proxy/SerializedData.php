<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Util\ArrayHelper;

class SerializedData implements ConverterInterface
{
    /**
     * @var ConverterInterface[]
     */
    private array $converters;

    /**
     * @throws ValidationException
     */
    public function __construct(array $parameters)
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
        $decoded = @unserialize((string) $value);
        if (!is_array($decoded)) {
            return $value;
        }

        foreach ($this->converters as $path => $converter) {
            // Get the value
            $nestedValue = ArrayHelper::getPath($decoded, $path);
            if ($nestedValue === null) {
                continue;
            }

            // Format the value
            $nestedValue = $converter->convert($nestedValue, $context);

            // Replace the original value in the JSON by the converted value
            ArrayHelper::setPath($decoded, $path, $nestedValue);
        }

        return serialize($decoded);
    }
}
