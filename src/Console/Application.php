<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public const VERSION = 'dev';

    public function __construct()
    {
        parent::__construct('GdprDump', self::VERSION);
    }
}
