<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Resource;

use Smile\GdprDump\Configuration\Exception\ParseException;
use stdClass;
use Symfony\Component\Yaml\Yaml;
use Throwable;

// TODO remove, it was just for testing
class ResourceParser
{
    public function parse(Resource $resource): stdClass
    {
        $parsed = $resource->isFile()
            ? $this->parseYamlFile($resource->getInput())
            : $this->parseJson($resource->getInput());

        return $parsed;
    }

    /**
     * Parse a YAML file.
     *
     * @throws ParseException
     */
    private function parseYamlFile(string $fileName): stdClass
    {
        try {
            // Parse yaml maps to stdClass and disallow use of `!php/object`
            $parsed = Yaml::parse($fileName, YAML::PARSE_OBJECT_FOR_MAP|Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);
        } catch (Throwable $e) {
            throw new ParseException(sprintf('Unable to parse the file "%s".', $fileName), $e);
        }

        if (!is_object($parsed)) {
            throw new ParseException(sprintf('The file "%s" could not be parsed into an object.', $fileName));
        }

        return $parsed;
    }

    /**
     * Parse a JSON string.
     */
    private function parseJson(string $input): stdClass
    {
        try {
            $parsed = json_decode($input, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new ParseException('Unable to parse the JSON input.', $e);
        }

        if (!is_object($parsed)) {
            throw new ParseException('The JSON input could not be parsed into an object.');
        }

        return $parsed;
    }
}
