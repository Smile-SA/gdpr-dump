<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Framework\Mock\Converter;

use Smile\GdprDump\Converter\ConverterInterface;

class ConverterMock implements ConverterInterface
{
    private string $prefix = 'test_';

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        if (array_key_exists('prefix', $parameters)) {
            $this->prefix = (string) $parameters['prefix'];
        }
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return $this->prefix . $value;
    }
}
