<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Parser;

use Smile\GdprDump\Config\Exception\ParseException;
use Smile\GdprDump\Config\Parser\Enum\Format;
use Smile\GdprDump\Config\Parser\Enum\Formats;
use stdClass;
use Symfony\Component\Yaml\Yaml;
use Throwable;

final class YamlFileParser implements Parser
{
    public function parse(string $input): stdClass
    {
        $contents = file_get_contents($input);
        if ($contents === false) {
            throw new ParseException(sprintf('The file "%s" is not readable.', $input));
        }

        try {
            $parsed = Yaml::parse($contents, YAML::PARSE_OBJECT_FOR_MAP);
        } catch (Throwable $e) {
            throw new ParseException(sprintf('Unable to parse the YAML file "%s".', $input), $e);
        }

        if (!is_object($parsed)) {
            throw new ParseException(sprintf('The file "%s" could not be parsed into an object.', $input));
        }

        return $parsed;
    }

    public function supports(Format $format): bool
    {
        return $format->getName() === Formats::YAML_FILE->value;
    }
}
