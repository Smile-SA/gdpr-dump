<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\ConfigLoader;
use Smile\GdprDump\Config\Parser\YamlParser;
use Smile\GdprDump\Config\Resolver\PathResolver;
use Smile\GdprDump\Tests\Unit\TestCase;

class ConfigLoaderTest extends TestCase
{
    /**
     * Test the "loadData" method.
     */
    public function testLoadData()
    {
        $data1 = ['key1' => 'value1'];
        $data2 = ['key2' => 'value2'];

        $config = new Config();
        $configLoader = new ConfigLoader($config, new YamlParser(), new PathResolver());

        $configLoader->loadData($data1);
        $this->assertSame($data1, $config->toArray());

        $configLoader->loadData($data2);
        $this->assertSame($data1 + $data2, $config->toArray());
    }

    /**
     * Test the "loadFile" method.
     */
    public function testLoadFile()
    {
        $config = new Config();
        $configLoader = new ConfigLoader($config, new YamlParser(), new PathResolver());

        $configLoader->loadFile($this->getTestConfigFile());

        $expectedSubset = ['table1' => ['truncate' => true]];
        $this->assertArraySubset($expectedSubset, $config->get('tables'));

        $expectedSubset = ['table2' => ['limit' => 1]];
        $this->assertArraySubset($expectedSubset, $config->get('tables'));

        $expectedSubset = ['table3' => ['orderBy' => 'field1']];
        $this->assertArraySubset($expectedSubset, $config->get('tables'));

        $expectedSubset = ['table4' => ['converters' => ['field1' => 'randomizeEmail']]];
        $this->assertArraySubset($expectedSubset, $config->get('tables'));
    }

    public function testLoadVersionData()
    {
        $config = new Config();
        $config->set('version', '2.0.0');
        $configLoader = new ConfigLoader($config, new YamlParser(), new PathResolver());

        $configLoader->loadFile($this->getTestConfigFile());
        $configLoader->loadVersionData();

        $expectedSubset = ['table3' => ['converters' => ['field1' => 'anonymizeEmail']]];
        $this->assertArraySubset($expectedSubset, $config->get('tables'));
    }

    /**
     * Check if an exception is thrown when the version was not specified.
     *
     * @expectedException \RuntimeException
     */
    public function testVersionNotSpecifiedException()
    {
        $config = new Config();
        $configLoader = new ConfigLoader($config, new YamlParser(), new PathResolver());
        $configLoader->loadFile($this->getTestConfigFile());
        $configLoader->loadVersionData();
    }
}
