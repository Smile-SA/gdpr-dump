<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database;

interface ConfigInterface
{
    /**
     * Get the database driver.
     *
     * @return string
     */
    public function getDriver(): string;

    /**
     * Get the driver options.
     *
     * @return array
     */
    public function getDriverOptions(): array;

    /**
     * Get the connection parameters (host, port, user...).
     *
     * @return array
     */
    public function getConnectionParams(): array;

    /**
     * Get the value of a connection parameter.
     *
     * @param string $name
     * @return mixed
     */
    public function getConnectionParam(string $name);
}
