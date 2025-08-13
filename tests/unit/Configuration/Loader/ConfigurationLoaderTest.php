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
        $expected = (object) [
            'processed' => true, // added by the mock processor
            'dump' => (object) ['output' => '%env(DUMP_OUTPUT)%'], // env var processor is not included in this test
            'tables' => (object) [
                'table1' => (object) [
                    'converters' => (object) [
                        'field1' => (object) ['converter' => 'randomizeEmail'],
                        'field2' => (object) ['converter' => 'anonymizeText'],
                    ],
                ],
                'table2' => (object) ['truncate' => true],
                'table3' => (object) ['limit' => 10, 'order_by' => 'field1'],
            ],
        ];

        $parsed = $this->createLoader()->load(
            new Resource(self::getResource('config/test_loader/config.yaml')),
            new Resource('{version: 2.0.0}', false)
        );

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
