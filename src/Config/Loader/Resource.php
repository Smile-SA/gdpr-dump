<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Loader;

use Smile\GdprDump\Config\Parser\Enum\Format;
use Smile\GdprDump\Config\Parser\Parser;

final class Resource
{
    public function __construct(
        private string $input,
        private Format $format,
        private Parser $parser,
    ) {
    }

    public function getInput(): string
    {
        return $this->input;
    }

    public function getFormat(): Format
    {
        return $this->format;
    }

    public function getParser(): Parser
    {
        return $this->parser;
    }
}
