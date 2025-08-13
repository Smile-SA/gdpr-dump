<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Resource;

use Stringable;

interface Resource extends Stringable
{
    public function getInput(): string;
}
