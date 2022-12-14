<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Mysql;

use Druidfi\Mysqldump\Mysqldump;
use Smile\GdprDump\Database\Database;
use Smile\GdprDump\Dumper\Config\DumperConfig;

class Context
{
    public function __construct(
        private Mysqldump $dumper,
        private Database $database,
        private DumperConfig $config,
        private array $dumperContext
    ) {
    }

    /**
     * Get the dumper config.
     */
    public function getConfig(): DumperConfig
    {
        return $this->config;
    }

    /**
     * Get the database wrapper.
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }

    /**
     * Get the dump context.
     */
    public function getDumper(): Mysqldump
    {
        return $this->dumper;
    }

    /**
     * Get the dumper context.
     */
    public function getDumperContext(): array
    {
        return $this->dumperContext;
    }
}
