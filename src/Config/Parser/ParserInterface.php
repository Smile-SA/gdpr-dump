<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Parser;

interface ParserInterface
{
    /**
     * Parse input into PHP.
     *
     * @throws ParseException
     */
    public function parse(string $input): mixed;
}
