<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Parser;

use Smile\GdprDump\Config\Exception\ParserNotFoundException;
use Smile\GdprDump\Config\Parser\JsonFileParser;
use Smile\GdprDump\Config\Parser\JsonParser;
use Smile\GdprDump\Config\Parser\ParserResolver;
use Smile\GdprDump\Config\Parser\YamlFileParser;
use Smile\GdprDump\Config\Resource\FileResource;
use Smile\GdprDump\Config\Resource\JsonResource;
use Smile\GdprDump\Tests\Unit\TestCase;

final class ParserResolverTest extends TestCase
{
    /**
     * Test the parser resolver.
     */
    public function testResolver(): void
    {
        $resolver = new ParserResolver([new YamlFileParser(), new JsonFileParser(), new JsonParser()]);

        $this->assertInstanceOf(YamlFileParser::class, $resolver->getParser(new FileResource('test.yaml')));
        $this->assertInstanceOf(JsonFileParser::class, $resolver->getParser(new FileResource('test.json')));
        $this->assertInstanceOf(JsonParser::class, $resolver->getParser(new JsonResource('{}')));

        // Default parser for files is yaml
        $this->assertInstanceOf(YamlFileParser::class, $resolver->getParser(new FileResource('test.php')));
    }

    /**
     * Test registering a default parser.
     */
    public function testRegisterDefaultParser(): void
    {
        $resolver = new ParserResolver([new YamlFileParser(), new JsonFileParser()], JsonFileParser::class);
        $this->assertInstanceOf(JsonFileParser::class, $resolver->getParser(new FileResource('test.php')));
    }

    /**
     * Assert that an exception is thrown when a parser is not found.
     */
    public function testParserNotFound(): void
    {
        $resolver = new ParserResolver([new YamlFileParser(), new JsonFileParser()]);

        $this->expectException(ParserNotFoundException::class);
        $resolver->getParser(new JsonResource('{}'));
    }

    /**
     * Assert that an exception is thrown when the default parser is not found.
     */
    public function testDefaultParserNotFound()
    {
        $resolver = new ParserResolver([new JsonFileParser()], JsonParser::class);

        $this->expectException(ParserNotFoundException::class);
        $resolver->getParser(new FileResource('test.yaml'));
    }
}
