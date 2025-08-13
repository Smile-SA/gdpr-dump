<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database;

use Doctrine\DBAL\Connection;
use Smile\GdprDump\Database\Driver\DatabaseDriver;
use Smile\GdprDump\Database\Metadata\DatabaseMetadata;

/**
 * Wrapper that stores the following objects:
 * - connection: the Doctrine connection.
 * - driver: allows to retrieve the DSN that was used to connect to the database.
 * - metadata: allows to fetch the database metadata (table names, foreign key constraints).
 *
 * We use a custom abstraction layer for database metadata, because the Doctrine schema manager
 * crashes when used with databases that use custom Doctrine types (e.g. OroCommerce).
 */
interface ConnectionProvider
{
    /**
     * Open the database connection.
     *
     * @throws DatabaseException
     */
    public function connect(): void;

    /**
     * Close the database connection.
     */
    public function close(): void;

    /**
     * Get the doctrine connection.
     */
    public function getConnection(): Connection;

    /**
     * Get the database driver.
     */
    public function getDriver(): DatabaseDriver;

    /**
     * Get the database metadata.
     */
    public function getMetadata(): DatabaseMetadata;

    /**
     * Get the connection parameters (host, port, user...).
     */
    public function getConnectionParams(): ParameterBag;
}
