<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Parser;

use Smile\GdprDump\Config\Exception\ParseException;
use Smile\GdprDump\Config\Parser\YamlFileParser;
use Smile\GdprDump\Config\Resource\FileResource;
use Smile\GdprDump\Tests\Unit\TestCase;

final class YamlFileParserTest extends TestCase
{
    /**
     * Test the parser.
     */
    public function testParser()
    {
        $parser = new YamlFileParser();
        $parsed = $parser->parse(new FileResource(self::getResource('parser/test.yaml')));
        $this->assertEquals((object) ['string' => 'value', 'array' => [1, 2, 3]], $parsed);
    }

    /**
     * Assert that an exception is thrown when the file does not exist.
     */
    public function testFileNotExists(): void
    {
        $parser = new YamlFileParser();

        $this->expectException(ParseException::class);
        $parser->parse(new FileResource(self::getResource('parser/not_exists.yaml')));
    }

    /**
     * Assert that an exception is thrown when the parsed input is invalid.
     */
    public function testInvalidInput()
    {
        $parser = new YamlFileParser();

        $this->expectException(ParseException::class);
        $parser->parse(new FileResource(self::getResource('parser/invalid_data.yaml')));
    }

    /**
     * Assert that an exception is thrown when the parsed input is not an object.
     */
    public function testInputIsNotAnObject(): void
    {
        $parser = new YamlFileParser();

        $this->expectException(ParseException::class);
        $parser->parse(new FileResource(self::getResource('parser/not_object.yaml')));
    }
}
