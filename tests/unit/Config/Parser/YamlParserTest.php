<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Parser;

use Smile\GdprDump\Config\Parser\YamlParser;
use Smile\GdprDump\Tests\Unit\TestCase;

class YamlParserTest extends TestCase
{
    /**
     * Test the parsing of YAML input.
     */
    public function testYamlInput()
    {
        $input = '{"object": {"key": "value"}}';
        $parser = new YamlParser();
        $result = $parser->parse($input);

        $this->assertSame(['object' => ['key' => 'value']], $result);
    }

    /**
     * Assert that an exception is thrown when the file is not found.
     *
     * @expectedException \Smile\GdprDump\Config\Parser\ParseException
     */
    public function testInvalidInput()
    {
        $parser = new YamlParser();
        $parser->parse('[invalid]Yaml');
    }
}
