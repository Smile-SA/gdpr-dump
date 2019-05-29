<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Dumper\Sql;

use Smile\Anonymizer\Config\Config;
use Smile\Anonymizer\Dumper\Sql\Config\DatabaseConfig;
use Smile\Anonymizer\Dumper\Sql\Config\Table\TableConfig;
use Smile\Anonymizer\Dumper\Sql\DumperConfig;
use Smile\Anonymizer\Dumper\Sql\TableFinder;
use Smile\Anonymizer\Tests\TestCase;
use Symfony\Component\Yaml\Yaml;

class DumperConfigTest extends TestCase
{
    /**
     * Test the dumper config.
     */
    public function testConfig()
    {
        $config = $this->createConfig();

        $this->assertInstanceOf(DatabaseConfig::class, $config->getDatabase());
        $this->assertSame('php://stdout', $config->getDumpOutput());
        $this->assertSame(['stores'], $config->getTablesToFilter());
        $this->assertSame(['customers'], $config->getTablesToSort());
        $this->assertCount(3, $config->getTablesConfig());
        $this->assertInstanceOf(TableConfig::class, $config->getTableConfig('customers'));
        $this->assertNotEmpty($config->getDumpSettings());
    }

    /**
     * Create a dumper config object.
     *
     * @return DumperConfig
     */
    private function createConfig(): DumperConfig
    {
        $data = Yaml::parseFile($this->getResource('config/test_config.yaml'));
        $config = new Config($data);

        $callback = function (string $pattern): array {
            return [$pattern];
        };

        $tableFinderMock = $this->createMock(TableFinder::class);
        $tableFinderMock->method('findByName')
            ->will($this->returnCallback($callback));

        /** @var TableFinder $tableFinderMock */
        return new DumperConfig($config, $tableFinderMock);
    }
}
