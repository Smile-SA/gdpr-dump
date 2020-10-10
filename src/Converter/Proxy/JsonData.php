<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use InvalidArgumentException;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Helper\ArrayHelper;
use UnexpectedValueException;

class JsonData implements ConverterInterface
{
    /**
     * @var ConverterInterface[]
     */
    private $converters;

    /**
     * @param array $parameters
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function __construct(array $parameters)
    {
        if (!array_key_exists('converters', $parameters)) {
            throw new InvalidArgumentException('The parameter "converters" is required.');
        }

        if (!is_array($parameters['converters'])) {
            throw new UnexpectedValueException('The parameter "converters" must be an array.');
        }

        if (empty($parameters['converters'])) {
            throw new UnexpectedValueException('The parameter "converters" must not be empty.');
        }

        $this->converters = $parameters['converters'];
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $decoded = json_decode($value, true);

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

        return json_encode($decoded);
    }
}
