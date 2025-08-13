<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Parser;

use Smile\GdprDump\Configuration\Resource\Resource;
use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Configuration\Exception\ParserNotFoundException;

final class ParserResolver
{
    /**
     * @param Parser[] $parsers
     */
    public function __construct(private iterable $parsers)
    {
    }

    /**
     * Get the parser than is able to process the specified resource.
     *
     * @throws ParseException
     */
    public function getParser(Resource $resource): Parser
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($resource)) {
                return $parser;
            }
        }

        throw new ParseException(
            $resource->isFile()
                ? sprintf('Unsupported file extension "%s".', $resource->getInput())
                : 'No compatible parser found.'
        );
    }
}
