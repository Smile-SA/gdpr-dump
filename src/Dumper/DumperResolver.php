<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper;

use Psr\Container\ContainerInterface;
use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Database\Driver\DatabaseDriver;
use Smile\GdprDump\Dumper\Exception\DumperNotFoundException;

final class DumperResolver
{
    public function __construct(private ContainerInterface $dumperLocator)
    {
    }

    /**
     * Get a dumper by driver name.
     *
     * @throws DumperNotFoundException
     */
    public function getDumper(Configuration $configuration): Dumper
    {
        $connectionParams = $configuration->getConnectionParams();
        $driver = $connectionParams['driver'] ?? DatabaseDriver::DEFAULT;

        if (!$this->dumperLocator->has($driver)) {
            throw new DumperNotFoundException(
                sprintf('No compatible dumper found for the driver "%s".', $driver)
            );
        }

        return $this->dumperLocator->get($driver);
    }
}
