<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\EventListener;

use Smile\GdprDump\Config\Event\LoadedEvent;
use Smile\GdprDump\Config\Validator\ValidationException;

final class DumpOutputListener
{
    /**
     * Process date placeholder in the dump output parameter.
     */
    public function __invoke(LoadedEvent $event): void
    {
        $config = $event->getConfig();

        $dump = $config->get('dump', []);
        if (!is_array($dump) || (array_key_exists('output', $dump) && !is_string($dump['output']))) {
            throw new ValidationException('Failed to parse the dump output.');
        }

        $output = $dump['output'] ?? '';
        if ($output !== '') {
            $dump['output'] = $this->processDatePlaceholder($dump['output']);
            $config->set('dump', $dump);
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
            throw new ValidationException('Failed to replace placeholders in value "%s".', $input);
        }

        return $input;
    }
}
