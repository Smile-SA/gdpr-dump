<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Parser;

use Smile\GdprDump\Config\Exception\ParserNotFoundException;
use Smile\GdprDump\Config\Resource\FileResource;
use Smile\GdprDump\Config\Resource\Resource;

final class ParserResolver
{
    /**
     * @param Parser[] $parsers
     */
    public function __construct(private iterable $parsers, private string $default = YamlFileParser::class)
    {
    }

    /**
     * Get the parser than is able to process the specified resource.
     *
     * @throws ParserNotFoundException
     */
    public function getParser(Resource $resource): Parser
    {
        $defaultParser = null;

        foreach ($this->parsers as $parser) {
            if ($parser->supports($resource)) {
                return $parser;
            }

            if ($parser instanceof $this->default) {
                $defaultParser = $parser;
            }
        }

        if (!$defaultParser || !$resource instanceof FileResource) {
            throw new ParserNotFoundException(
                sprintf('No compatible parser found for the resource %s.', $resource->__toString())
            );
        }

        return $defaultParser;
    }
}
