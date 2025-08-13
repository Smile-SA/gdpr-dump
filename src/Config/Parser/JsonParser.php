<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Parser;

use Smile\GdprDump\Config\Exception\ParseException;
use Smile\GdprDump\Config\Resource\JsonResource;
use Smile\GdprDump\Config\Resource\Resource;
use stdClass;
use Throwable;

final class JsonParser implements Parser
{
    public function parse(Resource $resource): stdClass
    {
        try {
            $parsed = json_decode($resource->getInput(), flags: JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new ParseException('Unable to parse the provided JSON input.', $e);
        }

        if (!is_object($parsed)) {
            throw new ParseException('The provided JSON input could not be parsed into an object.');
        }

        return $parsed;
    }

    public function supports(Resource $resource): bool
    {
        return $resource instanceof JsonResource;
    }
}
