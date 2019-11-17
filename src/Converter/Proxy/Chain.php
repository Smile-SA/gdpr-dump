<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use InvalidArgumentException;
use Smile\GdprDump\Converter\ConverterInterface;

class Chain implements ConverterInterface
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
        /** @var ConverterInterface $converter */
        foreach ($this->converters as $converter) {
            $value = $converter->convert($value, $context);
        }

        return $value;
    }
}
