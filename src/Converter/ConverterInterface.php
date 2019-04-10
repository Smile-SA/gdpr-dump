<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Converter;

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
