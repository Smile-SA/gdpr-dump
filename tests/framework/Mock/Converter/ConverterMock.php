<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Framework\Mock\Converter;

use Smile\GdprDump\Converter\ConverterInterface;

class ConverterMock implements ConverterInterface
{
    private string $prefix = 'test_';

    public function __construct(array $parameters = [])
    {
        if (array_key_exists('prefix', $parameters)) {
            $this->prefix = (string) $parameters['prefix'];
        }
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): mixed
    {
        return $this->prefix . $value;
    }
}
