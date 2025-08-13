<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional\Dumper\Config;

use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Configuration\Definition\TableConfig;
use Smile\GdprDump\Configuration\Definition\TableConfigMap;
use Smile\GdprDump\Dumper\Config\TableNameResolver;
use Smile\GdprDump\Tests\Functional\TestCase;

final class TableNameResolverTest extends TestCase
{
    /**
     * Test the table name resolution.
     */
    public function testTableNameResolution(): void
    {
        $configuration = (new Configuration())
            ->setExcludedTables(['stor*', 'notExist*'])
            ->setIncludedTables(['cust*', 'notExist*'])
            ->setTableConfigs(
                new TableConfigMap([
                    'cust*' => new TableConfig(),
                    'notExist*' => new TableConfig(),
                ])
            );

        $this->createResolver()->process($configuration);

        // Assert that table names were resolved
        $this->assertSame(['stores'], $configuration->getExcludedTables());
        $this->assertSame(['customers'], $configuration->getIncludedTables());

        $tableConfigs = $configuration->getTableConfigs();
        $this->assertTrue($tableConfigs->containsKey('customers'));
        $this->assertFalse($tableConfigs->containsKey('cust*'));
        $this->assertFalse($tableConfigs->containsKey('notExist*'));
    }

    /**
     * Create a table name resolver.
     */
    private function createResolver(): TableNameResolver
    {
        $metadata = $this->getDatabase()->getMetadata();

        return new TableNameResolver($metadata);
    }
}
