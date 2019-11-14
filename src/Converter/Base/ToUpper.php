<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Base;

use Smile\GdprDump\Converter\ConverterInterface;

class ToUpper implements ConverterInterface
{
    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return extension_loaded('mbstring') ? mb_strtoupper((string) $value, 'UTF-8') : strtoupper((string) $value);
    }
}
