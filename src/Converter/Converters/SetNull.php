<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Converters;

use Smile\GdprDump\Converter\ConverterInterface;

final class SetNull implements ConverterInterface
{
    /**
     * @inheritdoc
     */
    public function setParameters(array $parameters): void
    {
        // No parameters
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): mixed
    {
        return null;
    }
}
