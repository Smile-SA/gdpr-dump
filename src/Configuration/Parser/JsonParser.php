<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Parser;

use Smile\GdprDump\Configuration\Resource\Resource;
use Smile\GdprDump\Configuration\Exception\ParseException;
use stdClass;
use Symfony\Component\Yaml\Yaml;
use Throwable;

final class JsonParser implements Parser
{
    public function parse(Resource $resource): stdClass
    {
        try {
            // TODO
            //$parsed = json_decode($resource->getInput(), flags: JSON_THROW_ON_ERROR);
            $parsed = Yaml::parse($resource->getInput(), YAML::PARSE_OBJECT_FOR_MAP|Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);
        } catch (Throwable $e) {
            throw new ParseException('Unable to parse the JSON input.', $e);
        }

        if (!is_object($parsed)) {
            throw new ParseException('The JSON input could not be parsed into an object.');
        }

        return $parsed;
    }

    public function supports(Resource $resource): bool
    {
        return !$resource->isFile() && $resource->getType() === 'json';
    }
}
