<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Parser;

use Smile\GdprDump\Config\Exception\ParseException;
use Smile\GdprDump\Config\Resource\FileResource;
use Smile\GdprDump\Config\Resource\JsonResource;
use Smile\GdprDump\Config\Resource\Resource;
use stdClass;

final class JsonFileParser implements Parser
{
    private JsonParser $jsonParser;

    public function __construct()
    {
        $this->jsonParser = new JsonParser();
    }

    public function parse(Resource $resource): stdClass
    {
        $contents = file_get_contents($resource->getInput());
        if ($contents === false) {
            throw new ParseException(sprintf('The file "%s" is not readable.', $resource->getInput()));
        }

        return $this->jsonParser->parse(new JsonResource($contents));
    }

    public function supports(Resource $resource): bool
    {
        return $resource instanceof FileResource && $resource->getExtension() === 'json';
    }
}
