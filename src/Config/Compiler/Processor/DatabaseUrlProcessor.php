<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Compiler\Processor;

use Smile\GdprDump\Config\Compiler\CompileException;
use Smile\GdprDump\Config\ConfigInterface;

class DatabaseUrlProcessor implements ProcessorInterface
{
    /**
     * Replace environment variable placeholders (e.g. "%env(DB_HOST)%")
     */
    public function process(ConfigInterface $config): void
    {
        $data = $config->toArray();
        $data['database'] = $this->processDatabaseNode($config->toArray()['database'] ?? []);
        $config->reset($data);
    }

    /**
     * Process a config item.
     *
     * @throws CompileException
     */
    private function processDatabaseNode(array $data): array
    {
        if (isset($data['url'])) {
            $parsed = parse_url($data['url']);
            $mapped = [
                'name' => ltrim($parsed['path'] ?? '', '/'),
                'user' => $parsed['user'] ?? null,
                'password' => $parsed['pass'] ?? null,
                'host' => $parsed['host'] ?? null,
                'port' => $parsed['port'] ?? null,
                'driver' => !empty($parsed['scheme']) ? 'pdo_' . ltrim($parsed['scheme'], 'pdo_') : null,
            ];

            foreach ($mapped as $key => $value) {
                if (!isset($data[$key]) && (!empty($mapped[$key]))) {
                    $data[$key] = $value;
                }
            }
        }

        return $data;
    }
}
