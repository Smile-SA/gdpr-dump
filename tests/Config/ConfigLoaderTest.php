<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Config;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\ConfigLoader;
use Smile\GdprDump\Config\Parser\YamlParser;
use Smile\GdprDump\Config\Resolver\PathResolver;
use Smile\GdprDump\Tests\TestCase;

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

        $expectedSubset = ['customers' => ['converters' => ['email' => 'randomizeEmail']]];
        $this->assertArraySubset($expectedSubset, $config->get('tables'));
    }

    public function testLoadVersionData()
    {
        $config = new Config();
        $config->set('version', '2.0.0');
        $configLoader = new ConfigLoader($config, new YamlParser(), new PathResolver());

        $configLoader->loadFile($this->getTestConfigFile());
        $configLoader->loadVersionData();

        $expectedSubset = ['customers' => ['converters' => ['email' => 'anonymizeEmail']]];
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
