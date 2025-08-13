<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Parser;

use Smile\GdprDump\Configuration\Resource\Resource;
use Smile\GdprDump\Configuration\Exception\ParseException;
use stdClass;
use Symfony\Component\Yaml\Yaml;
use Throwable;

final class YamlFileParser implements Parser
{
    public function parse(Resource $resource): stdClass
    {
        $fileName = $resource->getInput();

        $input = file_get_contents($fileName);
        if ($input === false) {
            throw new ParseException(sprintf('The file "%s" is not readable.', $fileName));
        }

        try {
            // Parse yaml maps to stdClass and disallow use of `!php/object`
            $parsed = Yaml::parse($input, YAML::PARSE_OBJECT_FOR_MAP|Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);
        } catch (Throwable $e) {
            throw new ParseException(sprintf('Unable to parse the file "%s".', $fileName), $e);
        }

        if (!is_object($parsed)) {
            throw new ParseException(sprintf('The file "%s" could not be parsed into an object.', $fileName));
        }

        return $parsed;
    }

    public function supports(Resource $resource): bool
    {
        return $resource->isFile() && in_array($resource->getType(), ['yaml', 'yml'], true);
    }
}
