<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Builder;

use Smile\GdprDump\Config\DumperConfig;
use Smile\GdprDump\Database\Exception\InvalidParameterException;
use Smile\GdprDump\Util\Arrays;

final class ConnectionParamsBuilder
{
    /**
     * Build Doctrine connection parameters.
     */
    public function build(DumperConfig $config): array
    {
        $mapping = $this->getMapping();

        return Arrays::mapKeys(
            $config->getConnectionParams(),
            fn (string $key): string => $mapping[$key]
                ?? throw new InvalidParameterException(sprintf('The database setting "%s" is not supported.', $key))
        );
    }

    /**
     * Get the mapping between database settings and Doctrine connection parameters.
     */
    private function getMapping(): array
    {
        return [
            // This list must match the database object defined in schema.json
            // (except "url" which is removed by a config compiler)
            'charset' => 'charset',
            'driver' => 'driver',
            'driver_options' => 'driverOptions',
            'host' => 'host',
            'name' => 'dbname',
            'password' => 'password',
            'port' => 'port',
            'unix_socket' => 'unix_socket',
            'user' => 'user',
        ];
    }
}
