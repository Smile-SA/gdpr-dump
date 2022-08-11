<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Parser;

use Exception;
use Symfony\Component\Yaml\Yaml;

class YamlParser implements ParserInterface
{
    /**
     * @inheritdoc
     */
    public function parse(string $input): mixed
    {
        try {
            return Yaml::parse($input);
        } catch (Exception $e) {
            throw new ParseException('Unable to parse the YAML input.', $e);
        }
    }
}
