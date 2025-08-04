<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Builder;

use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Util\ArrayHelper;

final class ConnectionParamsBuilder
{
    public function __construct(private ArrayHelper $arrayHelper)
    {
    }

    /**
     * Build Doctrine connection parameters.
     */
    public function build(ConfigInterface $config): array
    {
        $settings = (array) $config->get('database', []);

        return $this->arrayHelper->map($settings, $this->getMapping());
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
