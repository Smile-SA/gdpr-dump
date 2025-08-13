<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Parser;

use Smile\GdprDump\Configuration\Resource\Resource;
use Smile\GdprDump\Configuration\Exception\ParseException;
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
