<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\EventListener;

use Smile\GdprDump\Config\Event\ConfigLoadedEvent;
use Smile\GdprDump\Config\Loader\EnvVarProcessor;

final class EnvVarListener
{
    public function __construct(private EnvVarProcessor $envVarProcessor)
    {
    }

    /**
     * Replace environment variable placeholders (e.g. `%env(DB_HOST)%`).
     *
     * Placeholders are replaced after the config was loaded, not during parsing of each config file.
     * The reason behind this logic is to ignore env vars that were replaced with something else in other config files.
     */
    public function __invoke(ConfigLoadedEvent $event): void
    {
        $config = $event->getConfigData();

        $this->envVarProcessor->process($config);
    }
}
