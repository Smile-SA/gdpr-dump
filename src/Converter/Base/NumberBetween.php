<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Base;

use InvalidArgumentException;
use UnexpectedValueException;
use Smile\GdprDump\Converter\ConverterInterface;

class NumberBetween implements ConverterInterface
{
    /**
     * @var int
     */
    private $min;

    /**
     * @var int
     */
    private $max;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        if (!array_key_exists('min', $parameters)) {
            throw new InvalidArgumentException('The parameter "min" is required.');
        }

        if (!array_key_exists('max', $parameters)) {
            throw new InvalidArgumentException('The parameter "max" is required.');
        }

        if ($parameters['min'] > $parameters['max']) {
            throw new UnexpectedValueException('The parameter "min" must be lower than the parameter "max".');
        }

        $this->min = (int) $parameters['min'];
        $this->max = (int) $parameters['max'];
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return mt_rand($this->min, $this->max);
    }
}
