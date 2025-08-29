<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper;

use Druidfi\Mysqldump\Mysqldump;
use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Database\Database;
use Smile\GdprDump\Database\DatabaseFactory;
use Smile\GdprDump\Database\Driver\DatabaseDriver;
use Smile\GdprDump\Dumper\Builder\MysqldumpHookBuilder;
use Smile\GdprDump\Dumper\Builder\MysqldumpSettingsBuilder;
use Smile\GdprDump\Dumper\Builder\MysqldumpWheresBuilder;
use Smile\GdprDump\Dumper\Config\TableNameResolver;
use Smile\GdprDump\Dumper\Event\DumpEvent;
use Smile\GdprDump\Dumper\Event\DumpFinishedEvent;
use Smile\GdprDump\Dumper\Exception\DumpException;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

#[AsTaggedItem(DatabaseDriver::MYSQL)]
final class MysqlDumper implements Dumper
{
    public function __construct(
        private DatabaseFactory $databaseFactory,
        private MysqldumpSettingsBuilder $mysqldumpSettingsBuilder,
        private MysqldumpHookBuilder $mysqldumpHookBuilder,
        private MysqldumpWheresBuilder $mysqldumpWheresBuilder,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function dump(Configuration $configuration, bool $dryRun = false): void
    {
        try {
            $dumpContext = new DumpContext();
            $configuration = clone $configuration; // object will be modified by ConfigProcessor

            // Initialize the database connection
            $database = $this->databaseFactory->create($configuration->getConnectionParams());
            $database->connect();

            // Process tables declared in the configuration (remove undefined tables, resolve patterns such as "log_*")
            $tableNameResolver = new TableNameResolver($database->getMetadata());
            $tableNameResolver->process($configuration);

            // Create the Mysqldump object (mysqldump-php library)
            $dumper = $this->createMysqldump($database, $configuration, $dumpContext);

            $this->eventDispatcher->dispatch(new DumpEvent($configuration, $dumper, $dumpContext, $database));

            // Close the Doctrine connection (mysqldump-php uses its own connection)
            $database->close();

            if (!$dryRun) {
                // Create the dump
                $dumper->start($configuration->getDumpSettings()->getOutput());
            }

            $this->eventDispatcher->dispatch(new DumpFinishedEvent($configuration));
        } catch (Throwable $e) {
            isset($database) && $database->close();
            throw $e instanceof DumpException ? $e : new DumpException($e->getMessage(), $e);
        }
    }

    /**
     * Create the Mysqldump object.
     */
    private function createMysqldump(
        Database $database,
        Configuration $configuration,
        DumpContext $dumpContext,
    ): Mysqldump {
        $dumpSettings = $this->mysqldumpSettingsBuilder->build($configuration);

        // Set SQL variables
        $connection = $database->getConnection();
        foreach ($configuration->getSqlVariables() as $varName => $query) {
            $value = $connection->fetchOne($query);
            $dumpContext->variables[$varName] = $value;
            $dumpSettings['init_commands'][] = 'SET @' . $varName . ' = ' . $connection->quote($value);
        }

        $dumper = new Mysqldump(
            $database->getDriver()->getDsn(),
            $database->getConnectionParams()->get('user'),
            $database->getConnectionParams()->get('password'),
            $dumpSettings,
            $database->getConnectionParams()->get('driverOptions', [])
        );

        $dumper->setTransformTableRowHook($this->mysqldumpHookBuilder->build($configuration, $dumpContext));
        $dumper->setTableWheres($this->mysqldumpWheresBuilder->build($configuration, $database));

        return $dumper;
    }
}
