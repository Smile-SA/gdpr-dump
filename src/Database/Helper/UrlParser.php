<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Helper;

use Smile\GdprDump\Database\Exception\InvalidUrlException;

// TODO unit tests
final class UrlParser
{
    /**
     * Parse a database URL.
     *
     * @return array{
     *     scheme: string,
     *     host: string,
     *     port: int,
     *     user: string,
     *     pass: string,
     *     path: string,
     *     query: string,
     *     fragment: string,
     * }
     */
    public function parse(string $url): array
    {
        // Validate url
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidUrlException(sprintf('The value "%s" is not a valid URL.', $url));
        }

        // Parse url
        $parsed = parse_url($url);
        if ($parsed === false) {
            throw new InvalidUrlException(sprintf('Failed to parse the url "%s".', $url));
        }

        return $parsed;
    }
}
