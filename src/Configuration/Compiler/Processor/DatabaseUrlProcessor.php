<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Compiler\Processor;

use Smile\GdprDump\Configuration\Compiler\ProcessorType;
use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Configuration\Loader\Container;
use Smile\GdprDump\Database\Driver\DatabaseDriver;
use Smile\GdprDump\Util\Url;
use stdClass;

class DatabaseUrlProcessor implements Processor
{
    public function getType(): ProcessorType
    {
        return ProcessorType::AFTER_VALIDATION;
    }

    /**
     * Parse database url (if specified).
     */
    public function process(Container $container): void
    {
        $database = $container->get('database');
        if (!$database) {
            return;
        }

        $url = $database->url ?? '';
        if ($url !== '') {
            $this->processDatabaseNode($url, $database);
        }

        unset($database->url);
    }

    /**
     * Parse the database url.
     */
    private function processDatabaseNode(string $url, stdClass $database): void
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
