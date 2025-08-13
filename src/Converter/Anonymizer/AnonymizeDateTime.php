<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Anonymizer;

use Smile\GdprDump\Converter\Converter;

final class AnonymizeDateTime implements Converter
{
    private AnonymizeDate $dateConverter;

    public function setParameters(array $parameters): void
    {
        if (!array_key_exists('format', $parameters)) {
            $parameters['format'] = 'Y-m-d H:i:s';
        }

        $this->dateConverter = new AnonymizeDate();
        $this->dateConverter->setParameters($parameters);
    }

    public function convert(mixed $value): string
    {
        return $this->dateConverter->convert($value);
    }
}
