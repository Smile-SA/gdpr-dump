<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

interface ConverterInterface
{
    /**
     * Transform the value.
     *
     * @param mixed $value
     * @param array $context
     * @return mixed
     */
    public function convert($value, array $context = []);
}
