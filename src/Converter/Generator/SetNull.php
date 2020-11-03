<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Generator;

use Smile\GdprDump\Converter\ConverterInterface;

class SetNull implements ConverterInterface
{
    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return null;
    }
}
