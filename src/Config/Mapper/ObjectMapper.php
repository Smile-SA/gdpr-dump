<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Mapper;

interface ObjectMapper
{
    public function map(object $from, object $to): void;
}
