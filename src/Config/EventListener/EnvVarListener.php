<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\EventListener;

use Smile\GdprDump\Config\EnvVarProcessor;
use Smile\GdprDump\Config\Event\ParseConfigEvent;
use Smile\GdprDump\Config\Helper\EnvVarProcessor as HelperEnvVarProcessor;

final class EnvVarListener
{
    public function __construct(private HelperEnvVarProcessor $processor)
    {
    }

    /**
     * Replace environment variable placeholders (e.g. "%env(DB_HOST)%").
     */
    public function __invoke(ParseConfigEvent $event): void
    {
        $config = $event->getConfigData();

        $this->processor->process($config);
    }
}
