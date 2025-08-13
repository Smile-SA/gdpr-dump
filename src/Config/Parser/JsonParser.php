<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Parser;

use Smile\GdprDump\Config\Exception\ParseException;
use Smile\GdprDump\Config\Parser\Enum\Format;
use Smile\GdprDump\Config\Parser\Enum\Formats;
use stdClass;
use Throwable;

final class JsonParser implements Parser
{
    public function parse(string $input): stdClass
    {
        try {
            $parsed = json_decode($input, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new ParseException('Unable to parse the provided JSON input.', $e);
        }

        if (!is_object($parsed)) {
            throw new ParseException(
                sprintf('The provided JSON input could not be parsed into an object.', $input)
            );
        }

        return $parsed;
    }

    public function supports(Format $format): bool
    {
        return $format->getName() === Formats::JSON->value;
    }
}
