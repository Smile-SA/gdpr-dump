<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Loader\Resource;

use Smile\GdprDump\Configuration\Exception\ParseException;
use stdClass;
use Symfony\Component\Yaml\Yaml;
use Throwable;

class ResourceParser
{
    /**
     * Parse the specified YAML resource.
     *
     * @throws ParseException
     */
    public function parse(Resource $resource): stdClass
    {
        $input = $resource->getInput();
        $descriptor = $this->getResourceDescriptor($resource);

        if ($resource->isFile()) {
            if (!is_file($input)) {
                throw new ParseException(sprintf('The file "%s" is not readable.', $input));
            }

            if (!is_readable($input)) {
                throw new ParseException(sprintf('The file "%s" is not readable.', $input));
            }

            $input = file_get_contents($input);
            if ($input === false) {
                throw new ParseException(sprintf('The file "%s" is not readable.', $resource->getInput()));
            }
        }

        return $this->parseYaml($input, $descriptor);
    }

    /**
     * Parse a YAML string.
     */
    private function parseYaml(string $input, string $descriptor): stdClass
    {
        try {
            // Parse yaml maps to stdClass and disallow use of `!php/object`
            $parsed = Yaml::parse($input, YAML::PARSE_OBJECT_FOR_MAP | Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);
        } catch (Throwable $e) {
            throw new ParseException(sprintf('Unable to parse the %s.', $descriptor), $e);
        }

        if (!$parsed instanceof stdClass) {
            throw new ParseException(sprintf('The %s could not be parsed into an object.', $descriptor));
        }

        return $parsed;
    }

    /**
     * Get the label that describes the resource (for use in exception messages).
     */
    private function getResourceDescriptor(Resource $resource): string
    {
        return $resource->isFile() ? sprintf('file "%s"', $resource->getInput()) : 'YAML input';
    }
}
