<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Transformer;

use Smile\GdprDump\Converter\ConverterInterface;

class ToUpper implements ConverterInterface
{
    /**
     * @var bool
     */
    private $multiByteEnabled;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Call the extension_loaded function only once (few seconds gain when converting millions of values)
        $this->multiByteEnabled = extension_loaded('mbstring');
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $value = (string) $value;

        return $value !== ''
            ? $this->multiByteEnabled ? mb_strtoupper($value, 'UTF-8') : strtoupper($value)
            : $value;
    }
}
