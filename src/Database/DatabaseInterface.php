<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database;

use Doctrine\DBAL\Connection;
use Smile\GdprDump\Database\Driver\DriverInterface;
use Smile\GdprDump\Database\Metadata\MetadataInterface;

/**
 * Wrapper that stores the following objects:
 * - connection: the Doctrine connection.
 * - driver: allows to retrieve the DSN that was used to connect to the database.
 * - metadata: allows to fetch the database metadata (table names, foreign key constraints).
 *
 * We use a custom abstraction layer for database metadata, because the Doctrine schema manager
 * crashes when used with databases that use custom Doctrine types (e.g. OroCommerce).
 */
interface DatabaseInterface
{
    public const DRIVER_MYSQL = 'pdo_mysql';

    /**
     * Get the doctrine connection.
     */
    public function getConnection(): Connection;

    /**
     * Get the database driver.
     */
    public function getDriver(): DriverInterface;

    /**
     * Get the database metadata.
     */
    public function getMetadata(): MetadataInterface;

    /**
     * Get the connection parameters (host, port, user...).
     */
    public function getConnectionParams(): ParameterBag;
}
