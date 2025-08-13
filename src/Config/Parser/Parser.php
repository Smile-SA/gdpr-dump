<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Parser;

use Smile\GdprDump\Config\Exception\ParseException;
use Smile\GdprDump\Config\Parser\Enum\Format;
use stdClass;

interface Parser
{
    /**
     * Parse the specified input.
     *
     * @throws ParseException
     */
    public function parse(string $input): stdClass;

    /**
     * Check whether the parser is able to process a resource.
     */
    public function supports(Format $format): bool;
}
