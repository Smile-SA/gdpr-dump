<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Parser;

use Smile\GdprDump\Config\Parser\YamlParser;
use Smile\GdprDump\Tests\Unit\TestCase;
use Symfony\Component\Yaml\Yaml;

class YamlParserTest extends TestCase
{
    /**
     * Test the parsing of a YAML file.
     */
    public function testParseFile()
    {
        $fileName = $this->getTestConfigFile();

        $parser = new YamlParser();
        $result = $parser->parse($fileName);

        $this->assertSame(Yaml::parseFile($fileName), $result);
    }

    /**
     * Test if an exception is thrown when the file is not found.
     *
     * @expectedException \Smile\GdprDump\Config\Parser\ParseException
     */
    public function testFileNotFound()
    {
        $parser = new YamlParser();
        $parser->parse('notExists.yaml');
    }
}
