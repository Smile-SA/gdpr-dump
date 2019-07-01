<?php
declare(strict_types=1);

namespace Smile\GdprDump\Console;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    const VERSION = '1.0.0-beta3';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('GdprDump', self::VERSION);
    }

    /**
     * Get the config path.
     *
     * @return string
     */
    public function getConfigPath()
    {
        return APP_ROOT . '/config';
    }

    /**
     * Get the vendor path.
     *
     * @return string
     */
    public function getVendorPath()
    {
        return APP_ROOT . '/vendor';
    }
}
