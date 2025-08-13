<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\EventListener;

use Smile\GdprDump\Config\Event\ConfigLoadedEvent;
use Smile\GdprDump\Config\Exception\ConfigLoadException;

// TODO switch to a new event ConfigMappedEvent
final class DumpOutputListener
{
    /**
     * Process date placeholder in the dump output parameter.
     */
    public function __invoke(ConfigLoadedEvent $event): void
    {
        $config = $event->getConfigData();
        if (!property_exists($config, 'dump')) {
            return;
        }

        $dump = $config->dump;
        if (!is_object($dump) || (property_exists($dump, 'output') && !is_string($dump->output))) {
            throw new ConfigLoadException('Failed to parse the dump output.');
        }

        $output = $dump->output ?? '';
        if ($output !== '') {
            $dump->output = $this->processDatePlaceholder($dump->output);
        }
    }

    /**
     * Replace date placeholders.
     */
    private function processDatePlaceholder(string $input): string
    {
        $input = preg_replace_callback(
            '/{([^}]+)}/',
            fn (array $matches): string => date($matches[1]),
            $input
        );

        if ($input === null) {
            throw new ConfigLoadException(sprintf('Failed to replace placeholders in value "%s".', $input));
        }

        return $input;
    }
}
