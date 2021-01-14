<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Mysqldump;

use Ifsnop\Mysqldump\Mysqldump;
use Smile\GdprDump\Database\Database;
use Smile\GdprDump\Dumper\Config\DumperConfig;

class Context
{
    /**
     * @var Mysqldump
     */
    private $dumper;

    /**
     * @var Database
     */
    private $database;

    /**
     * @var DumperConfig
     */
    private $config;

    /**
     * @var array
     */
    private $dumperContext;

    /**
     * @param Mysqldump $dumper
     * @param Database $database
     * @param DumperConfig $config
     * @param array $dumperContext
     */
    public function __construct(
        Mysqldump $dumper,
        Database $database,
        DumperConfig $config,
        array $dumperContext
    ) {
        $this->dumper = $dumper;
        $this->database = $database;
        $this->config = $config;
        $this->dumperContext = $dumperContext;
    }

    /**
     * Get the dumper config.
     *
     * @return DumperConfig
     */
    public function getConfig(): DumperConfig
    {
        return $this->config;
    }

    /**
     * Get the database wrapper.
     *
     * @return Database
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }

    /**
     * Get the dump context.
     *
     * @return Mysqldump
     */
    public function getDumper(): Mysqldump
    {
        return $this->dumper;
    }

    /**
     * Get the dumper context.
     *
     * @return array
     */
    public function getDumperContext(): array
    {
        return $this->dumperContext;
    }
}
