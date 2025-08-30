<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration;

use Smile\GdprDump\Configuration\Compiler\CompilerStep;
use Smile\GdprDump\Configuration\Compiler\ConfigurationCompiler;
use Smile\GdprDump\Configuration\Compiler\Processor\Processor;
use Smile\GdprDump\Configuration\Loader\ConfigurationLoader;
use Smile\GdprDump\Configuration\Loader\Container;
use Smile\GdprDump\Configuration\Loader\Env\EnvVarParser;
use Smile\GdprDump\Configuration\Loader\Resource\Resource;
use Smile\GdprDump\Configuration\Loader\Resource\ResourceFactory;
use Smile\GdprDump\Configuration\Loader\Resource\ResourceLocator;
use Smile\GdprDump\Configuration\Loader\Resource\ResourceParser;
use Smile\GdprDump\Configuration\Loader\Version\VersionApplier;
use Smile\GdprDump\Configuration\Validator\JsonSchemaValidator;
use Smile\GdprDump\Tests\Unit\TestCase;
use stdClass;

final class ConfigurationLoaderTest extends TestCase
{
    /**
     * Test the configuration loader.
     */
    public function testLoader(): void
    {
        $container = new Container();
        $loader = $this->createLoader();
        $fileResource = new Resource(self::getResource('config/test_loader/config.yaml'));

        // Test with version 1.0.0 (base config, no if_version block matches this version)
        $expected = $this->getExpectedDataForDefaultVersion();
        $loader->load($container, $fileResource, new Resource('{version: 1.0.0}', false));

        $this->assertTrue($container->has('if_version'));
        $container->remove('if_version');
        $this->assertEquals($expected, $container->getRoot());

        // Test with version 2.0.0 (matching if_version block must be merged)
        $container = new Container();
        $expected = $this->getExpectedDataForDefaultVersion();
        $expected->version = '2.0.0';
        $expected->tables->table1->where = '1=1';
        $this->createLoader()->load($container, $fileResource, new Resource('{version: 2.0.0}', false));
        $this->assertTrue($container->has('if_version'));
        $container->remove('if_version');
        $this->assertEquals($expected, $container->getRoot());

        // Test with version 3.0.0 (matching if_version block must be merged)
        $container = new Container();
        $expected = $this->getExpectedDataForDefaultVersion();
        $expected->version = '3.0.0';
        $expected->tables->table1 = (object) ['limit' => null, 'truncate' => true];
        $expected->tables->table2->converters->field1->converter = 'anonymizeEmail';
        $expected->tables->table3 = (object) ['order_by' => 'field1'];

        $this->createLoader()->load($container, $fileResource, new Resource('{version: 3.0.0}', false));
        $this->assertTrue($container->has('if_version'));
        $container->remove('if_version');
        $this->assertEquals($expected, $container->getRoot());
    }

    /**
     * Test the configuration loader when no resources are provided.
     */
    public function testLoadEmptyResources(): void
    {
        $container = new Container();
        $loader = $this->createLoader();
        $loader->load($container);
        $this->assertSame(['strict_schema' => true], $container->toArray());
    }

    /**
     * Get the data supposed to be parsed when version 1.0.0 is specified.
     */
    private function getExpectedDataForDefaultVersion(): stdClass
    {
        return (object) [
            'version' => '1.0.0',
            'strict_schema' => true, // added by the mock processor
            'dump' => (object) ['output' => '%env(DUMP_OUTPUT)%'], // env var processor is not included in this test
            'tables' => (object) [
                'table1' => (object) ['limit' => 100],
                'table2' => (object) [
                    'converters' => (object) [
                        'field1' => (object) ['converter' => 'randomizeEmail'],
                        'field2' => (object) ['converter' => 'randomizeText'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Create the loader object.
     */
    private function createLoader(): ConfigurationLoader
    {
        // Create a mock processor to make sure that processors are triggered
        $processorMock = new class implements Processor {
            public function getStep(): CompilerStep
            {
                return CompilerStep::BEFORE_VALIDATION;
            }

            public function process(Container $container): void
            {
                $container->set('strict_schema', true);
            }
        };

        return new ConfigurationLoader(
            new ResourceParser(),
            new ResourceFactory(new ResourceLocator(self::getResource('config/test_loader/templates'))),
            new VersionApplier(new EnvVarParser()),
            new ConfigurationCompiler(
                new JsonSchemaValidator(self::getBasePath() . '/app/config/schema.json'),
                [$processorMock]
            )
        );
    }
}
