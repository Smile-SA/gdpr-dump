<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Mysqldump;

use Ifsnop\Mysqldump\Mysqldump;

interface ExtensionInterface
{
    /**
     * Register the extension.
     *
     * @param Mysqldump $dumper
     */
    public function register(Mysqldump $dumper): void;
}
