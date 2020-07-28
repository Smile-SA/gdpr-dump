<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Randomizer;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Generator\RandomDate;
use Smile\GdprDump\Converter\Parameters\ValidationException;

/**
 * @deprecated Use "randomDate" instead.
 */
class RandomizeDate implements ConverterInterface
{
    /**
     * @var ConverterInterface
     */
    private ConverterInterface $converter;

    /**
     * @param array $parameters
     * @throws ValidationException
     */
    public function __construct(array $parameters = [])
    {
        $this->converter = new RandomDate($parameters);
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return $this->converter->convert($value, $context);
    }
}
