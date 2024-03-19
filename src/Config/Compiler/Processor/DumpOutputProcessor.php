<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Compiler\Processor;

use Smile\GdprDump\Config\ConfigInterface;

class DumpOutputProcessor implements ProcessorInterface
{
    /**
     * Process date placeholders in dump output (e.g. "dump-{Y-m-d-H.i.s}.sql").
     */
    public function process(ConfigInterface $config): void
    {
        $dumpSettings = $config->get('dump', []);

        if (array_key_exists('output', $dumpSettings)) {
            $dumpSettings['output'] = preg_replace_callback(
                '/{([^}]+)}/',
                fn (array $matches) => date($matches[1]),
                $dumpSettings['output']
            );

            $config->set('dump', $dumpSettings);
        }
    }
}
