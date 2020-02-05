<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

class Dummy implements ConverterInterface
{
    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return $value;
    }
}
