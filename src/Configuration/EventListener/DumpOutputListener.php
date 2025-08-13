<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\EventListener;

use Smile\GdprDump\Configuration\Event\ConfigParsedEvent;
use Smile\GdprDump\Configuration\Exception\ParseException;

// TODO switch to a new event ConfigMappedEvent
final class DumpOutputListener
{
    /**
     * Process date placeholder in the dump output parameter.
     */
    public function __invoke(ConfigParsedEvent $event): void
    {
        $configuration = $event->getConfigurationData();
        if (!property_exists($configuration, 'dump')) {
            return;
        }

        $dump = $configuration->dump;
        if (!is_object($dump) || (property_exists($dump, 'output') && !is_string($dump->output))) {
            throw new ParseException('Failed to parse the dump output.');
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
            throw new ParseException(sprintf('Failed to replace placeholders in value "%s".', $input));
        }

        return $input;
    }
}
