<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\EventListener;

use Smile\GdprDump\Configuration\Event\ConfigParsedEvent;
use Smile\GdprDump\Database\Driver\DatabaseDriver;
use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Util\Url;

// TODO switch to a new event ConfigMappedEvent
final class DatabaseUrlListener
{
    /**
     * If a database url is set, update the connection params defined in the configuration.
     */
    public function __invoke(ConfigParsedEvent $event): void
    {
        $configuration = $event->getConfigurationData();
        if (!property_exists($configuration, 'database')) {
            return;
        }

        $database = $configuration->database;
        if (!is_object($database) || (property_exists($database, 'url') && !is_string($database->url))) {
            throw new ParseException('Failed to parse the database url.');
        }

        $url = $database->url ?? '';
        if ($url !== '') {
            $this->processDatabaseNode($url, $database);
        }
    }

    /**
     * Parse the database url.
     */
    private function processDatabaseNode(string $url, object $database): void
    {
        $parsedUrl = Url::parse($url);

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
            if (!array_key_exists($urlPart, $parsedUrl) || property_exists($database, $dbParam)) {
                // Parameter was not found in url or is already defined in the database array
                continue;
            }

            $value = (string) $parsedUrl[$urlPart];
            $database->$dbParam = match ($dbParam) {
                'name' => ltrim($value, '/'),
                'driver' => $this->getDriverByScheme($value),
                default => $value
            };
        }

        unset($database->url);
    }

    /**
     * Get driver by scheme.
     */
    private function getDriverByScheme(string $scheme): string
    {
        return match ($scheme) {
            'mysql' => DatabaseDriver::MYSQL,
            default => throw new ParseException(sprintf('Invalid scheme "%s".', $scheme))
        };
    }
}
