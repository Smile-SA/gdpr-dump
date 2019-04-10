<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Console;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    const VERSION = '0.1.0';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('anonymizer', self::VERSION);
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
