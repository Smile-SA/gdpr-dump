<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper;

use Smile\GdprDump\Config\DumperConfig;
use Smile\GdprDump\Database\DatabaseFactory;
use Smile\GdprDump\Database\Driver\DatabaseDriver;
use Smile\GdprDump\Dumper\Builder\MysqldumpSettingsBuilder;
use Smile\GdprDump\Dumper\Exception\DumperNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

// TODO unit tests
final class DumperFactory
{
    public function __construct(
        private DatabaseFactory $databaseFactory,
        private MysqldumpSettingsBuilder $mysqldumpSettingsBuilder,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * Get a dumper by driver name.
     */
    public function create(DumperConfig $config): Dumper
    {
        $connectionParams = $config->getConnectionParams();
        $driver = $connectionParams['driver'] ?? DatabaseDriver::DEFAULT;

        return match ($driver) {
            DatabaseDriver::MYSQL => new MysqlDumper(
                $this->databaseFactory,
                $this->mysqldumpSettingsBuilder,
                $this->eventDispatcher
            ),
            default => throw new DumperNotFoundException(
                sprintf('No compatible dumper found for the driver "%s".', $driver)
            ),
        };
    }
}
