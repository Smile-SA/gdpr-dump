<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration;

use Smile\GdprDump\Configuration\Loader\ConfigurationLoader;
use Smile\GdprDump\Configuration\Loader\Env\EnvVarParser;
use Smile\GdprDump\Configuration\Loader\Processor\Processor;
use Smile\GdprDump\Configuration\Loader\Resource\Resource;
use Smile\GdprDump\Configuration\Loader\Resource\ResourceFactory;
use Smile\GdprDump\Configuration\Loader\Resource\ResourceLocator;
use Smile\GdprDump\Configuration\Loader\Resource\ResourceParser;
use Smile\GdprDump\Configuration\Loader\Version\VersionApplier;
use Smile\GdprDump\Tests\Unit\TestCase;
use stdClass;

final class ConfigurationLoaderTest extends TestCase
{
    /**
     * Test the configuration loader.
     */
    public function testLoader(): void
    {
        $loader = $this->createLoader();
        $fileResource = new Resource(self::getResource('config/test_loader/config.yaml'));

        // Test with version 1.0.0 (base config, no if_version block matches this version)
        $expected = $this->getExpectedDataForDefaultVersion();
        $parsed = $loader->load($fileResource, new Resource('{version: 1.0.0}', false));
        $this->assertObjectHasProperty('if_version', $parsed);
        unset($parsed->if_version);

        $this->assertEquals($expected, $parsed);

        // Test with version 2.0.0 (matching if_version block must be merged)
        $expected = $this->getExpectedDataForDefaultVersion();
        $expected->version = '2.0.0';
        $expected->tables->table1->where = '1=1';
        $parsed = $this->createLoader()->load($fileResource, new Resource('{version: 2.0.0}', false));
        $this->assertObjectHasProperty('if_version', $parsed);
        unset($parsed->if_version);
        $this->assertEquals($expected, $parsed);

        // Test with version 3.0.0 (matching if_version block must be merged)
        $expected = $this->getExpectedDataForDefaultVersion();
        $expected->version = '3.0.0';
        $expected->tables->table2->converters->field1->converter = 'anonymizeEmail';
        $expected->tables->table3 = (object) ['order_by' => 'field1'];
        unset($expected->tables->table2->converters->field3);
        $parsed = $this->createLoader()->load($fileResource, new Resource('{version: 3.0.0}', false));
        $this->assertObjectHasProperty('if_version', $parsed);
        unset($parsed->if_version);
        $this->assertEquals($expected, $parsed);
    }

    /**
     * Test the configuration loader when no resources are provided.
     */
    public function testLoadEmptyResources(): void
    {
        $loader = $this->createLoader();
        $parsed = $loader->load();

        $this->assertSame(['processed' => true], get_object_vars($parsed));
    }

    /**
     * Get the data supposed to be parsed when version 1.0.0 is specified.
     */
    private function getExpectedDataForDefaultVersion(): stdClass
    {
        return (object) [
            'version' => '1.0.0',
            'processed' => true, // added by the mock processor
            'dump' => (object) ['output' => '%env(DUMP_OUTPUT)%'], // env var processor is not included in this test
            'tables' => (object) [
                'table1' => (object) ['limit' => null, 'truncate' => true],
                'table2' => (object) [
                    'converters' => (object) [
                        'field1' => (object) ['converter' => 'randomizeEmail'],
                        'field2' => (object) ['converter' => 'randomizeText'],
                        'field3' => (object) ['converter' => 'randomizeText'],
                    ],
                ],
            ],
        ];
    }

    private function createLoader(): ConfigurationLoader
    {
        // Create a mock processor to make sure that processors are triggered
        $processorMock = new class implements Processor {
            public function process(stdClass $configuration): void
            {
                $configuration->processed = true;
            }
        };

        // Create the loader without the processors, they are already tested independently
        return new ConfigurationLoader(
            new ResourceParser(),
            new ResourceFactory(new ResourceLocator(self::getResource('config/test_loader/templates'))),
            new VersionApplier(new EnvVarParser()),
            [$processorMock]
        );
    }
}
