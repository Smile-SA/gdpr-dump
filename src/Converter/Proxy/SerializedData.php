<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use InvalidArgumentException;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Helper\ArrayHelper;

class SerializedData implements ConverterInterface
{
    /**
     * @var ConverterInterface[]
     */
    private $converters;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        if (empty($parameters['converters'])) {
            throw new InvalidArgumentException('The parameter "converters" is required.');
        }

        $this->converters = (array) $parameters['converters'];
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $decoded = @unserialize($value);

        if (!is_array($decoded)) {
            return $value;
        }

        /** @var ConverterInterface $converter */
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

        $encoded = serialize($decoded);

        return $encoded;
    }
}
