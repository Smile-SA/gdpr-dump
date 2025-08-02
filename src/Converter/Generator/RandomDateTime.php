<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Generator;

use Smile\GdprDump\Converter\ConverterInterface;

final class RandomDateTime implements ConverterInterface
{
    private RandomDate $dateConverter;

    public function setParameters(array $parameters): void
    {
        if (!array_key_exists('format', $parameters)) {
            $parameters['format'] = 'Y-m-d H:i:s';
        }

        $this->dateConverter = new RandomDate();
        $this->dateConverter->setParameters($parameters);
    }

    public function convert(mixed $value): string
    {
        return $this->dateConverter->convert($value);
    }
}
