<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Config;

use Smile\Anonymizer\Config\Config;
use Smile\Anonymizer\Config\ConfigLoader;
use Smile\Anonymizer\Config\Parser\YamlParser;
use Smile\Anonymizer\Config\Resolver\PathResolver;
use Smile\Anonymizer\Tests\TestCase;

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
