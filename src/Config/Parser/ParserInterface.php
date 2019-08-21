<?php
declare(strict_types=1);

namespace Smile\GdprDump\Config\Parser;

interface ParserInterface
{
    /**
     * Parse input into PHP.
     *
     * @param string $input
     * @return mixed
     * @throws ParseException
     */
    public function parse(string $input);
}
