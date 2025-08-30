<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Compiler\Processor;

use Smile\GdprDump\Configuration\Compiler\ProcessorType;
use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Configuration\Loader\Container;

class DumpOutputProcessor implements Processor
{
    public function getType(): ProcessorType
    {
        return ProcessorType::AFTER_VALIDATION;
    }

    /**
     * Process placeholders in the dump output setting.
     */
    public function process(Container $container): void
    {
        $dump = $container->get('dump');
        if (!$dump) {
            return;
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
