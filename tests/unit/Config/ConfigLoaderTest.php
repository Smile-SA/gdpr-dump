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
     * Test the "loadFile" method.
     */
    public function testLoadFile()
    {
        $config = new Config();
        $configLoader = $this->createConfigLoader($config);
        $configLoader->loadFile($this->getTestConfigFile());

        $expectedSubset = ['table1' => ['converters' => ['field1' => ['converter' => 'randomizeEmail']]]];
        $this->assertArraySubset($expectedSubset, $config->get('tables'));

        $expectedSubset = ['table1' => ['converters' => ['field2' => ['converter' => 'anonymizeText']]]];
        $this->assertArraySubset($expectedSubset, $config->get('tables'));

        $expectedSubset = ['table2' => ['truncate' => true]];
        $this->assertArraySubset($expectedSubset, $config->get('tables'));

        $expectedSubset = ['table3' => ['limit' => 10]];
        $this->assertArraySubset($expectedSubset, $config->get('tables'));

        $expectedSubset = ['table4' => ['orderBy' => 'field1']];
        $this->assertArraySubset($expectedSubset, $config->get('tables'));
    }

    /**
     * Test the "loadVersionData" method.
     */
    public function testLoadVersionData()
    {
        $config = new Config();
        $configLoader = $this->createConfigLoader($config);
        $configLoader->loadFile($this->getTestConfigFile());
        $configLoader->loadVersionData();

        $expectedSubset = ['table1' => ['converters' => ['field1' =>  ['disabled' => true]]]];
        $this->assertArraySubset($expectedSubset, $config->get('tables'));
    }

    /**
     * Check if an exception is thrown when the config file is not found.
     *
     * @expectedException \Smile\GdprDump\Config\Resolver\FileNotFoundException
     */
    public function testFileNotFoundException()
    {
        $config = new Config();
        $configLoader = $this->createConfigLoader($config);
        $configLoader->loadFile('not_exists.yaml');
    }

    /**
     * Check if an exception is thrown when the version was not specified.
     *
     * @expectedException \Smile\GdprDump\Config\Parser\ParseException
     */
    public function testVersionNotSpecifiedException()
    {
        $config = new Config();
        $configLoader = $this->createConfigLoader($config);
        $configLoader->loadFile($this->getTestConfigFile());

        $config->set('version', null);
        $configLoader->loadVersionData();
    }

    /**
     * Check if an exception is thrown when the parsed data is not an array.
     *
     * @expectedException \Smile\GdprDump\Config\Parser\ParseException
     */
    public function testDataIsNotAnArray()
    {
        $config = new Config();
        $configLoader = $this->createConfigLoader($config);
        $configLoader->loadFile(static::getResource('config/templates/invalid_data.yaml'));
    }

    /**
     * Check if an exception is thrown when the version condition is badly formatted.
     *
     * @expectedException \Smile\GdprDump\Config\Parser\ParseException
     */
    public function testInvalidVersionFormatException()
    {
        $config = new Config();
        $config->set('version', '1.0.0');
        $config->set('if_version', ['notValid' => []]);

        $configLoader = $this->createConfigLoader($config);
        $configLoader->loadVersionData();
    }

    /**
     * Create a config loader object.
     *
     * @param Config $config
     * @return ConfigLoader
     */
    private function createConfigLoader(Config $config): ConfigLoader
    {
        $templatesDirectory = $this->getResource('config/templates');

        return new ConfigLoader($config, new YamlParser(), new PathResolver($templatesDirectory));
    }

    /**
     * Get the config file used for the tests.
     *
     * @return string
     */
    private static function getTestConfigFile(): string
    {
        return static::getResource('config/templates/test.yaml');
    }
}
