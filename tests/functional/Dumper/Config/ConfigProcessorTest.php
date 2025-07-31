<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional\Dumper\Config;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Dumper\Config\ConfigProcessor;
use Smile\GdprDump\Tests\Functional\TestCase;

final class ConfigProcessorTest extends TestCase
{
    /**
     * Test the table name resolution.
     */
    public function testTableNameResolution(): void
    {
        $config = new Config([
            'tables_blacklist' => ['stor*', 'notExist*'],
            'tables_whitelist' => ['cust*', 'notExist*'],
            'tables' => [
                'cust*' => [],
                'notExist*' => [],
            ],
        ]);

        $processor = $this->createConfigProcessor();
        $processor->process($config);

        // Assert that table names were resolved
        $this->assertSame(['stores'], $config->get('tables_blacklist'));
        $this->assertSame(['customers'], $config->get('tables_whitelist'));

        $tablesData = $config->get('tables');
        $this->assertIsArray($tablesData);
        $this->assertArrayHasKey('customers', $tablesData);
        $this->assertArrayNotHasKey('cust*', $tablesData);
        $this->assertArrayNotHasKey('notExist*', $tablesData);
    }

    /**
     * Create a config processor object.
     */
    private function createConfigProcessor(): ConfigProcessor
    {
        $metadata = $this->getDatabase()->getMetadata();

        return new ConfigProcessor($metadata);
    }
}
