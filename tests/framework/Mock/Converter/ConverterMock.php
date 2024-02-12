<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Framework\Mock\Converter;

use Smile\GdprDump\Converter\ConverterInterface;

class ConverterMock implements ConverterInterface
{
    private string $prefix = 'test_';

    /**
     * @inheritdoc
     */
    public function setParameters(array $parameters): void
    {
        if (array_key_exists('prefix', $parameters)) {
            $this->prefix = (string) $parameters['prefix'];
        }
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): string
    {
        return $this->prefix . $value;
    }
}
