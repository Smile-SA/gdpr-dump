<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database;

interface ConfigInterface
{
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
     * @param mixed $default
     * @return mixed
     */
    public function getConnectionParam(string $name, $default = null);
}
