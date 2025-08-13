<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Parser;

use Smile\GdprDump\Configuration\Resource\Resource;
use Smile\GdprDump\Configuration\Exception\ParseException;
use stdClass;
use Throwable;

final class JsonFileParser implements Parser
{
    public function parse(Resource $resource): stdClass
    {
        $fileName = $resource->getInput();

        $input = file_get_contents($fileName);
        if ($input === false) {
            throw new ParseException(sprintf('The file "%s" is not readable.', $fileName));
        }

        try {
            $parsed = json_decode($input, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new ParseException(sprintf('Unable to parse the file "%s".', $fileName), $e);
        }

        if (!is_object($parsed)) {
            throw new ParseException(sprintf('The file "%s" could not be parsed into an object.', $fileName));
        }

        return $parsed;
    }

    public function supports(Resource $resource): bool
    {
        return $resource->isFile() && $resource->getType() === 'json';
    }
}
