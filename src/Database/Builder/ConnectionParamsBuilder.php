<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Builder;

use RuntimeException;
use Smile\GdprDump\Config\ConfigInterface;

final class ConnectionParamsBuilder
{
    /**
     * Build Doctrine connection parameters.
     */
    public function build(ConfigInterface $config): array
    {
        $parameters = (array) $config->get('database', []);
        $mapping = $this->getMapping();

        foreach ($parameters as $key => $value) {
            if (!array_key_exists($key, $mapping)) {
                throw new RuntimeException(sprintf('The dump setting "%s" does not exist.', $key));
            }

            if ($mapping[$key] !== $key) {
                $parameters[$mapping[$key]] = $value;
                unset($parameters[$key]);
            }
        }

        return $parameters;
    }

    /**
     * Get the mapping between GdprDump parameters and Doctrine parameters.
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
