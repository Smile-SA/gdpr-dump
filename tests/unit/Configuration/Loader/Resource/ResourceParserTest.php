<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Loader\Resource;

use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Configuration\Loader\Resource\Resource;
use Smile\GdprDump\Configuration\Loader\Resource\ResourceParser;
use Smile\GdprDump\Tests\Unit\TestCase;

final class ResourceParserTest extends TestCase
{
    /**
     * Test the parser with a file resource.
     */
    public function testParseFile(): void
    {
        $parser = new ResourceParser();
        $parsed = $parser->parse(new Resource(self::getResource('config/test_parser/config.yaml')));
        $this->assertEquals((object) ['string' => 'value', 'array' => [1, 2, 3]], $parsed);
    }

    /**
     * Test the parser with a string resource.
     */
    public function testParseString(): void
    {
        $parser = new ResourceParser();
        $parsed = $parser->parse(new Resource('{foo: bar}', false));
        $this->assertEquals((object) ['foo' => 'bar'], $parsed);
    }

    /**
     * Assert that an exception is thrown when the file does not exist.
     */
    public function testFileNotExists(): void
    {
        $parser = new ResourceParser();

        $this->expectException(ParseException::class);
        $parser->parse(new Resource(self::getResource('config/test_parser/not_exists.yaml')));
    }

    /**
     * Assert that an exception is thrown when the parsed input is invalid.
     */
    public function testInvalidInput(): void
    {
        $parser = new ResourceParser();

        $this->expectException(ParseException::class);
        $parser->parse(new Resource('not => allowed', false));
    }

    /**
     * Assert that an exception is thrown when the parsed input is not an object.
     */
    public function testInputIsNotAnObject(): void
    {
        $parser = new ResourceParser();

        $this->expectException(ParseException::class);
        $parser->parse(new Resource('not_an_object', false));
    }
}
