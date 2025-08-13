<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Parser;

use Smile\GdprDump\Config\Exception\ParseException;
use Smile\GdprDump\Config\Resource\FileResource;
use Smile\GdprDump\Config\Resource\Resource;
use stdClass;
use Symfony\Component\Yaml\Yaml;
use Throwable;

final class YamlFileParser implements Parser
{
    public function parse(Resource $resource): stdClass
    {
        $contents = file_get_contents($resource->getInput());
        if ($contents === false) {
            throw new ParseException(sprintf('The file "%s" is not readable.', $resource->getInput()));
        }

        try {
            $parsed = Yaml::parse($contents, YAML::PARSE_OBJECT_FOR_MAP);
        } catch (Throwable $e) {
            throw new ParseException(sprintf('Unable to parse the YAML file "%s".', $resource->getInput()), $e);
        }

        if (!is_object($parsed)) {
            throw new ParseException(
                sprintf('The file "%s" could not be parsed into an object.', $resource->getInput())
            );
        }

        return $parsed;
    }

    public function supports(Resource $resource): bool
    {
        return $resource instanceof FileResource && in_array($resource->getExtension(), ['yaml', 'yml'], true);
    }
}
