<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config;

use ArrayAccess;
use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\ConfigLoader;
use Smile\GdprDump\Config\Parser\ParseException;
use Smile\GdprDump\Config\Parser\YamlParser;
use Smile\GdprDump\Config\Processor\EnvVarProcessor;
use Smile\GdprDump\Config\Resolver\FileNotFoundException;
use Smile\GdprDump\Config\Resolver\PathResolver;
use Smile\GdprDump\Tests\Unit\TestCase;

class ConfigLoaderTest extends TestCase
{
    /**
     * Test the "loadFile" method.
     */
    public function testLoadFile(): void
    {
        $config = new Config();
        $configLoader = $this->createConfigLoader($config);
        $configLoader->loadFile($this->getTestConfigFile());
        $tablesConfig = $config->get('tables');

        $expectedSubset = ['output' => 'dump.sql'];
        $this->assertArraySubset($expectedSubset, $config->get('dump'));

        $expectedSubset = ['table1' => ['converters' => ['field1' => ['converter' => 'randomizeEmail']]]];
        $this->assertArraySubset($expectedSubset, $tablesConfig);

        $expectedSubset = ['table1' => ['converters' => ['field2' => ['converter' => 'anonymizeText']]]];
        $this->assertArraySubset($expectedSubset, $tablesConfig);

        $expectedSubset = ['table2' => ['truncate' => true]];
        $this->assertArraySubset($expectedSubset, $tablesConfig);

        $expectedSubset = ['table3' => ['limit' => 10]];
        $this->assertArraySubset($expectedSubset, $tablesConfig);

        $expectedSubset = ['table4' => ['orderBy' => 'field1']];
        $this->assertArraySubset($expectedSubset, $tablesConfig);
    }

    /**
     * Assert that an exception is thrown when the config file is not found.
     */
    public function testFileNotFoundException(): void
    {
        $config = new Config();
        $configLoader = $this->createConfigLoader($config);

        $this->expectException(FileNotFoundException::class);
        $configLoader->loadFile('not_exists.yaml');
    }

    /**
     * Assert that an exception is thrown when the parsed data is not an array.
     */
    public function testDataIsNotAnArray(): void
    {
        $config = new Config();
        $configLoader = $this->createConfigLoader($config);

        $this->expectException(ParseException::class);
        $configLoader->loadFile(static::getResource('config/templates/invalid_data.yaml'));
    }

    /**
     * Assert that an array is a subset of another array.
     *
     * @param array $subset
     * @param array $array
     */
    private function assertArraySubset(array $subset, array $array): void
    {
        $this->assertTrue($this->isArraySubset($subset, $array));
    }

    /**
     * Check if the array is a subset of another array.
     *
     * @param array $subset
     * @param array $array
     * @return bool
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function isArraySubset(array $subset, array $array): bool
    {
        foreach ($subset as $key => $value) {
            if (!array_key_exists($key, $array)) {
                return false;
            }

            if (is_array($value)) {
                if (!$this->isArraySubset($value, $array[$key])) {
                    return false;
                }
            } elseif ($value !== $array[$key]) {
                return false;
            }
        }

        return true;
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

        $processorMock = $this->createMock(EnvVarProcessor::class);
        $processorMock->method('process')
            ->willReturnCallback(function ($value) {
                return $value === '%env(DUMP_OUTPUT)%' ? 'dump.sql' : $value;
            });

        return new ConfigLoader($config, new YamlParser(), [$processorMock], new PathResolver($templatesDirectory));
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
