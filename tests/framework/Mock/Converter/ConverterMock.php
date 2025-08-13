<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Framework\Mock\Converter;

use Smile\GdprDump\Converter\Converter;

final class ConverterMock implements Converter
{
    private string $prefix = 'test_';

    public function setParameters(array $parameters): void
    {
        if (array_key_exists('prefix', $parameters)) {
            $this->prefix = (string) $parameters['prefix'];
        }
    }

    public function convert(mixed $value): string
    {
        return $this->prefix . $value;
    }
}
