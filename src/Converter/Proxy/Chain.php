<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use InvalidArgumentException;
use Smile\GdprDump\Converter\ConverterInterface;
use UnexpectedValueException;

class Chain implements ConverterInterface
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
        foreach ($this->converters as $converter) {
            $value = $converter->convert($value, $context);
        }

        return $value;
    }
}
