<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Converters;

final class AnonymizeDateTime extends AnonymizeDate
{
    protected string $defaultFormat = 'Y-m-d H:i:s';
}
