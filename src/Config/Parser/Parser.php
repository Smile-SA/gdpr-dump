<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Parser;

use Smile\GdprDump\Config\Exception\ParseException;
use Smile\GdprDump\Config\Resource\Resource;
use stdClass;

interface Parser
{
    /**
     * Parse the specified resource.
     *
     * @throws ParseException
     */
    public function parse(Resource $resource): stdClass;

    /**
     * Check whether the parser is able to process a resource.
     */
    public function supports(Resource $resource): bool;
}
