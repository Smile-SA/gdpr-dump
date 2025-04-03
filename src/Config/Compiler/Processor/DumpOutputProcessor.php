<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Compiler\Processor;

use Smile\GdprDump\Config\Compiler\CompileException;
use Smile\GdprDump\Config\ConfigInterface;

final class DumpOutputProcessor implements ProcessorInterface
{
    /**
     * Process placeholders in the dump output setting.
     */
    public function process(ConfigInterface $config): void
    {
        $dumpSettings = (array) $config->get('dump');
        $dumpSettings['output'] = $this->processDatePlaceholder((string) $dumpSettings['output']);
        $config->set('dump', $dumpSettings);
    }

    /**
     * Replace date placeholders.
     *
     * @throws CompileException
     */
    private function processDatePlaceholder(string $input): string
    {
        $input = preg_replace_callback(
            '/{([^}]+)}/',
            fn (array $matches) => date($matches[1]),
            $input
        );

        if ($input === null) {
            throw new CompileException('Failed to replace placeholders in value "%s".', $input);
        }

        return $input;
    }
}
