<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Randomizer;

use Closure;
use Smile\GdprDump\Converter\ConverterInterface;

class RandomizeNumber implements ConverterInterface
{
    private Closure $replaceCallback;

    public function __construct()
    {
        $this->replaceCallback = fn () => mt_rand(0, 9);
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): mixed
    {
        $value = (string) $value;

        return $value !== ''
            ? preg_replace_callback('/[0-9]/', $this->replaceCallback, $value)
            : $value;
    }
}
