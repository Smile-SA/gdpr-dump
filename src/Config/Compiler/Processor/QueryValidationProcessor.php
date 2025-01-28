<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Compiler\Processor;

use Exception;
use Smile\GdprDump\Config\ConfigException;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Util\QueryValidator;

final class QueryValidationProcessor implements ProcessorInterface
{
    /**
     * Assert that SQL queries don't include forbidden keywords.
     */
    public function process(ConfigInterface $config): void
    {
        try {
            $this->validateVarQueries($config);
            $this->validateInitCommands($config);
        } catch (Exception $e) {
            throw new ConfigException($e->getMessage(), $e);
        }
    }

    /**
     * Validate SQL queries found in the "variables" section of the config.
     */
    private function validateVarQueries(ConfigInterface $config): void
    {
        $selectQueryValidator = new QueryValidator(['select']);
        $varQueries = (array) $config->get('variables');

        foreach ($varQueries as $query) {
            $selectQueryValidator->validate($query);
        }
    }

    /**
     * Validate SQL queries found in the "dump.init_commands" section of the config.
     */
    private function validateInitCommands(ConfigInterface $config): void
    {
        $selectQueryValidator = new QueryValidator(['set']);
        $dumpSettings = (array) $config->get('dump');

        foreach ($dumpSettings['init_commands'] as $query) {
            $selectQueryValidator->validate($query);
        }
    }
}
