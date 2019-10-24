<?php
declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Sql;

use Doctrine\DBAL\Connection;
use Smile\GdprDump\Dumper\Sql\Driver\DriverInterface;
use Smile\GdprDump\Dumper\Sql\Metadata\MetadataInterface;

interface DatabaseInterface
{
    /**
     * Get the doctrine connection.
     *
     * @return Connection
     */
    public function getConnection(): Connection;

    /**
     * Get the database driver.
     *
     * @return DriverInterface
     */
    public function getDriver(): DriverInterface;

    /**
     * Get the database metadata.
     *
     * @return MetadataInterface
     */
    public function getMetadata(): MetadataInterface;
}
