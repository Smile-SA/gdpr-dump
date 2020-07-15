<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * Application version.
     */
    const VERSION = '2.0.0';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('GdprDump', self::VERSION);
    }
}
