<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Randomizer;

use Closure;
use Smile\GdprDump\Converter\ConverterInterface;

class RandomizeNumber implements ConverterInterface
{
    /**
     * @var Closure
     */
    private $replaceCallback;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->replaceCallback = function (): int {
            return mt_rand(0, 9);
        };
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $value = (string) $value;

        return $value !== ''
            ? preg_replace_callback('/[0-9]/', $this->replaceCallback, $value)
            : $value;
    }
}
