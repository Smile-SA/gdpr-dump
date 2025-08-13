<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\EventListener;

use Smile\GdprDump\Configuration\Event\ConfigParsedEvent;
use Smile\GdprDump\Configuration\Loader\EnvVarProcessor;

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
    public function __invoke(ConfigParsedEvent $event): void
    {
        $configuration = $event->getConfigurationData();

        $this->envVarProcessor->process($configuration);
    }
}
