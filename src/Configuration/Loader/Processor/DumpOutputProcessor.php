<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Loader\Processor;

use Smile\GdprDump\Configuration\Exception\ParseException;
use stdClass;

class DumpOutputProcessor implements Processor
{
    /**
     * Process placeholders in the dump output setting.
     */
    public function process(stdClass $configuration): void
    {
        if (
            !property_exists($configuration, 'dump')
            || !$configuration->dump instanceof stdClass
            || !property_exists($configuration->dump, 'output')
            || !is_string($configuration->dump->output)
        ) {
            return;
        }

        if ($configuration->dump->output !== '') {
            $configuration->dump->output = $this->processDatePlaceholder($configuration->dump->output);
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
