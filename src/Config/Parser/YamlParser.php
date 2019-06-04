<?php
declare(strict_types=1);

namespace Smile\GdprDump\Config\Parser;

use Symfony\Component\Yaml\Yaml;

class YamlParser implements ParserInterface
{
    /**
     * @inheritdoc
     */
    public function parse(string $fileName)
    {
        try {
            return Yaml::parseFile($fileName);
        } catch (\Exception $e) {
            throw new ParseException(sprintf('Failed to parse the file "%s".', $fileName), $e);
        }
    }
}
