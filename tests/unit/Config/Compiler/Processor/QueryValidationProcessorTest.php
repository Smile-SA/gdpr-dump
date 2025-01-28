<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Compiler\Processor;

use Smile\GdprDump\Config\Compiler\Processor\QueryValidationProcessor;
use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\ConfigException;
use Smile\GdprDump\Tests\Unit\TestCase;

final class QueryValidationProcessorTest extends TestCase
{
    /**
     * Assert that no exception is thrown when the config contains valid SQL queries.
     */
    public function testValidQueries(): void
    {
        $data = $this->getData();
        $data['variables'][] = 'select `id` from `table1` where `value` = "bar"';
        $data['dump']['init_commands'][] =
            'SET SESSION SQL_MODE="STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER"';
        $config = new Config($data);

        $processor = new QueryValidationProcessor();
        $this->expectNotToPerformAssertions();
        $processor->process($config);
    }

    /**
     * Assert that an exception is thrown when the "variables" param contains an invalid SQL query.
     */
    public function testInvalidVariableQuery(): void
    {
        $data = $this->getData();
        $data['variables'][] = 'update `table1` set `value` = "bar" where `id` = 1';
        $config = new Config($data);

        $processor = new QueryValidationProcessor();
        $this->expectException(ConfigException::class);
        $processor->process($config);
    }

    /**
     * Assert that an exception is thrown when the "init_commands" param contains an invalid SQL query.
     */
    public function testInvalidInitQuery(): void
    {
        $data = $this->getData();
        $data['dump']['init_commands'][] = 'update `table1` set `value` = "bar" where `id` = 1';
        $config = new Config($data);

        $processor = new QueryValidationProcessor();
        $this->expectException(ConfigException::class);
        $processor->process($config);
    }

    /**
     * Get sample config data.
     */
    private function getData(): array
    {
        return [
            'dump' => ['init_commands' => []],
            'variables' => [],
        ];
    }
}
