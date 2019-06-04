<?php
declare(strict_types=1);

namespace Smile\GdprDump\Config\Parser;

interface ParserInterface
{
    /**
     * Parse a config file.
     *
     * @param string $fileName
     * @return mixed
     * @throws ParseException
     */
    public function parse(string $fileName);
}
