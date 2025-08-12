<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\EventListener;

use Smile\GdprDump\Config\Event\LoadedEvent;
use Smile\GdprDump\Config\Validator\ValidationException;
use Smile\GdprDump\Database\DatabaseInterface;

final class DatabaseUrlListener
{
    /**
     * If a database url is set, update the connection params defined in the configuration.
     */
    public function __invoke(LoadedEvent $event): void
    {
        $config = $event->getConfig();

        $database = $config->get('database');
        if (!is_array($database) || (array_key_exists('url', $database) && !is_string($database['url']))) {
            throw new ValidationException('Failed to parse the database url.');
        }

        $url = $database['url'] ?? '';
        if ($url !== '') {
            $database = $this->processDatabaseNode($url, $database);
            $config->set('database', $database);
        }
    }

    /**
     * Parse the database url.
     */
    private function processDatabaseNode(string $url, array $database): array
    {
        // Validate url
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new ValidationException(sprintf('The value "%s" is not a valid URL.', $url));
        }

        // Parse url
        $parsedUrl = parse_url($url);
        if ($parsedUrl === false) {
            throw new ValidationException(sprintf('Failed to parse the url "%s".', $url));
        }

        // Update database params from parsed url
        $map = [
            'scheme' => 'driver',
            'path' => 'name',
            'host' => 'host',
            'port' => 'port',
            'user' => 'user',
            'pass' => 'password',
        ];

        foreach ($map as $urlPart => $dbParam) {
            if (!array_key_exists($urlPart, $parsedUrl) || array_key_exists($dbParam, $database)) {
                // Parameter was not found in url or is already defined in the database array
                continue;
            }

            $value = (string) $parsedUrl[$urlPart];
            $database[$dbParam] = match ($dbParam) {
                'name' => ltrim($value, '/'),
                'driver' => $this->getDriverByScheme($value),
                default => $value
            };
        }

        unset($database['url']);

        return $database;
    }

    /**
     * Get driver by scheme.
     */
    private function getDriverByScheme(string $scheme): string
    {
        return match ($scheme) {
            'mysql' => DatabaseInterface::DRIVER_MYSQL,
            default => throw new ValidationException(sprintf('Invalid scheme "%s".', $scheme))
        };
    }
}
