<?php
declare(strict_types=1);

namespace Smile\GdprDump\Console;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * Application version.
     */
    const VERSION = '1.0.0-beta15';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('GdprDump', self::VERSION);
    }
}
