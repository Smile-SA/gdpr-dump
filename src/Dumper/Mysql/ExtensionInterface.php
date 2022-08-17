<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Mysql;

interface ExtensionInterface
{
    /**
     * Register the extension.
     *
     * @param Context $context
     */
    public function register(Context $context): void;
}
