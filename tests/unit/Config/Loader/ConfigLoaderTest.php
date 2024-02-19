<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Loader;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\Loader\ConfigLoader;
use Smile\GdprDump\Config\Loader\FileLocator;
use Smile\GdprDump\Config\Loader\FileNotFoundException;
use Smile\GdprDump\Config\Loader\ParseException;
use Smile\GdprDump\Tests\Unit\TestCase;

class ConfigLoaderTest extends TestCase
{
    /**
     * Test the "load" method.
     */
    public function testLoad(): void
    {
        $config = new Config(['version' => '2.0.0']);
        $configLoader = $this->createConfigLoader();
        $configLoader->load($this->getResource('config/templates/test.yaml'), $config);

        $expectedSubset = ['output' => '%env(DUMP_OUTPUT)%'];
        $this->assertArraySubset($expectedSubset, $config->get('dump'));

        $tablesConfig = $config->get('tables');
        $expectedSubset = ['table1' => ['converters' => ['field1' => ['converter' => 'randomizeEmail']]]];
        $this->assertArraySubset($expectedSubset, $tablesConfig);

        $expectedSubset = ['table1' => ['converters' => ['field2' => ['converter' => 'anonymizeText']]]];
        $this->assertArraySubset($expectedSubset, $tablesConfig);

        $expectedSubset = ['table2' => ['truncate' => true]];
        $this->assertArraySubset($expectedSubset, $tablesConfig);

        $expectedSubset = ['table3' => ['limit' => 10]];
        $this->assertArraySubset($expectedSubset, $tablesConfig);

        $expectedSubset = ['table4' => ['order_by' => 'field1']];
        $this->assertArraySubset($expectedSubset, $tablesConfig);

        // Assert that the converters of table 4 were removed (by setting it to null in the child config file)
        $this->assertArrayNotHasKey('converters', $tablesConfig['table4']);
    }

    /**
     * Assert that an exception is thrown when the config file is not found.
     */
    public function testFileNotFoundException(): void
    {
        $config = new Config();
        $configLoader = $this->createConfigLoader();

        $this->expectException(FileNotFoundException::class);
        $configLoader->load('not_exists.yaml', $config);
    }

    /**
     * Assert that an exception is thrown when the parsed data is not an array.
     */
    public function testDataIsNotAnArray(): void
    {
        $config = new Config();
        $configLoader = $this->createConfigLoader();

        $this->expectException(ParseException::class);
        $configLoader->load($this->getResource('config/templates/invalid_data.yaml'), $config);
    }

    /**
     * Assert that an array is a subset of another array.
     */
    private function assertArraySubset(array $subset, array $array): void
    {
        $this->assertTrue($this->isArraySubset($subset, $array));
    }

    /**
     * Check if the array is a subset of another array.
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
     */
    private function createConfigLoader(): ConfigLoader
    {
        $templatesDirectory = $this->getResource('config/templates');

        return new ConfigLoader(new FileLocator($templatesDirectory));
    }
}
