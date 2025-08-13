<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Parser;

use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Configuration\Parser\JsonFileParser;
use Smile\GdprDump\Configuration\Resource\FileResource;
use Smile\GdprDump\Tests\Unit\TestCase;

final class JsonFileParserTest extends TestCase
{
    /**
     * Test the parser.
     */
    public function testParser()
    {
        $parser = new JsonFileParser();
        $parsed = $parser->parse(new FileResource(self::getResource('parser/test.json')));
        $this->assertEquals((object) ['string' => 'value', 'array' => [1, 2, 3]], $parsed);
    }

    /**
     * Assert that an exception is thrown when the file does not exist.
     */
    public function testFileNotExists(): void
    {
        $parser = new JsonFileParser();

        $this->expectException(ParseException::class);
        $parser->parse(new FileResource(self::getResource('parser/not_exists.json')));
    }

    /**
     * Assert that an exception is thrown when the parsed input is invalid.
     */
    public function testInvalidInput()
    {
        $parser = new JsonFileParser();

        $this->expectException(ParseException::class);
        $parser->parse(new FileResource(self::getResource('parser/invalid_data.json')));
    }

    /**
     * Assert that an exception is thrown when the parsed input is not an object.
     */
    public function testInputIsNotAnObject(): void
    {
        $parser = new JsonFileParser();

        $this->expectException(ParseException::class);
        $parser->parse(new FileResource(self::getResource('parser/not_object.json')));
    }
}
