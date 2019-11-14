<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Base;

use Smile\GdprDump\Converter\ConverterInterface;

class ToLower implements ConverterInterface
{
    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return extension_loaded('mbstring') ? mb_strtolower((string) $value, 'UTF-8') : strtolower((string) $value);
    }
}
