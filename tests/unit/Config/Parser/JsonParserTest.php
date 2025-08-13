<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Parser;

use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Configuration\Parser\JsonParser;
use Smile\GdprDump\Configuration\Resource\JsonResource;
use Smile\GdprDump\Tests\Unit\TestCase;

final class JsonParserTest extends TestCase
{
    /**
     * Test the parser.
     */
    public function testParser()
    {
        $data = ['string' => 'value', 'array' => [1, 2, 3]];

        $parser = new JsonParser();
        $parsed = $parser->parse(new JsonResource(json_encode($data)));
        $this->assertEquals((object) $data, $parsed);
    }

    /**
     * Assert that an exception is thrown when the parsed input is invalid.
     */
    public function testInvalidInput()
    {
        $parser = new JsonParser();

        $this->expectException(ParseException::class);
        $parser->parse(new JsonResource('{not: allowed}'));
    }

    /**
     * Assert that an exception is thrown when the parsed input is not an object.
     */
    public function testInputIsNotAnObject(): void
    {
        $parser = new JsonParser();

        $this->expectException(ParseException::class);
        $parser->parse(new JsonResource('string value'));
    }
}
