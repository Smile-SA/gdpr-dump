<?php

declare(strict_types=1);

namespace Smile\GdprDump\Util;

use Doctrine\DBAL\Driver\PgSQL\Exception\UnexpectedValue;
use UnexpectedValueException;

final class Url
{
    /**
     * Parse a database URL.
     *
     * @return array{
     *     scheme?: string,
     *     host?: string,
     *     port?: int,
     *     user?: string,
     *     pass?: string,
     *     path?: string,
     *     query?: string,
     *     fragment?: string,
     * }
     * @throws UnexpectedValue
     */
    public static function parse(string $url): array
    {
        // Validate url
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new UnexpectedValueException(sprintf('The value "%s" is not a valid URL.', $url));
        }

        // Parse url
        $parsed = parse_url($url);
        if ($parsed === false) {
            throw new UnexpectedValueException(sprintf('Failed to parse the url "%s".', $url));
        }

        return $parsed;
    }
}
