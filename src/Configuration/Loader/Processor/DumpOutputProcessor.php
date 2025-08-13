<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Loader\Processor;

use Smile\GdprDump\Configuration\Exception\ParseException;
use stdClass;

class DumpOutputProcessor implements Processor
{
    /**
     * Process the configuration.
     */
    public function process(stdClass $configuration): void
    {
        if (!property_exists($configuration, 'dump')) {
            return;
        }

        $dump = $configuration->dump;
        if (
            !is_object($dump)
            || !$dump instanceof stdClass
            || (property_exists($dump, 'output') && !is_string($dump->output))
        ) {
            throw new ParseException('Failed to parse the dump output.');
        }

        $output = $dump->output ?? '';
        if ($output !== '') {
            $dump->output = $this->processDatePlaceholder($output);
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
