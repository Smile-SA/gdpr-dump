<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\EventListener;

use Smile\GdprDump\Configuration\Event\ParseConfigurationEvent;
use Smile\GdprDump\Configuration\Event\MergeResourceEvent;
use Smile\GdprDump\Configuration\Event\ParseResourceEvent;
use Smile\GdprDump\Configuration\EventListener\VersionListener;
use Smile\GdprDump\Configuration\Exception\InvalidVersionException;
use Smile\GdprDump\Configuration\Loader\EnvVarProcessor;
use Smile\GdprDump\Tests\Unit\TestCase;
use stdClass;

final class VersionListenerTest extends TestCase
{
    /**
     * Assert that "if_version" blocks are processed successfully.
     */
    public function testVersionListener(): void
    {
        $data = (object) [
            'items' => (object) ['key' => 'value'],
            'if_version' => (object) [
                '>=1.0.0 <2.0.0' => (object) [
                    'items' => (object) ['key' => 'new_value_1'],
                    'new_key' => 1,
                ],
                '>=2.0.0' => (object) [
                    'items' => (object) ['key' => 'new_value_2'],
                ],
            ],
        ];

        // Test with version "1.0.0"
        $data->version = '1.0.0';
        $processedData = $this->processVersions($data);
        //$this->assertSame('1.0.0', $configuration->get('version')); // TODO
        $this->assertSame('new_value_1', $processedData->items->key);
        $this->assertObjectHasProperty('new_key', $processedData);
        $this->assertSame(1, $processedData->new_key);

        // Test with version "2.0.0"
        $data->version = '2.0.0';
        $processedData = $this->processVersions($data);
        //$this->assertSame('2.0.0', $configuration->get('version'));
        $this->assertSame('new_value_2', $processedData->items->key);
        $this->assertObjectNotHasProperty('new_key', $processedData);
    }

    /**
     * Assert that an exception is thrown when the version was not specified.
     */
    public function testVersionNotSpecifiedException(): void
    {
        $this->expectException(InvalidVersionException::class);
        $this->processVersions((object) ['if_version' => (object) ['>=2.0.0' => (object) ['key' => 'value']]]);
    }

    /**
     * Assert that an exception is thrown when the version is missing.
     */
    public function testMissingVersion(): void
    {
        $this->expectException(InvalidVersionException::class);
        $this->processVersions((object) ['if_version' => (object) ['>2.0.0' => (object) ['key' => 'value']]]);
    }

    /**
     * Assert that an exception is thrown when the version has an invalid type.
     */
    public function testInvalidVersionType(): void
    {
        $this->expectException(InvalidVersionException::class);
        $this->processVersions((object) ['version' => ['not a string']]);
    }

    /**
     * Assert that an exception is thrown when the version data has an invalid type.
     */
    public function testInvalidVersionsData(): void
    {
        $this->expectException(InvalidVersionException::class);
        $this->processVersions((object) ['version' => '>2.0.0', 'if_version' => 'not an array']);
    }

    /**
     * Create and run the event listener with the specified configuration.
     */
    private function processVersions(stdClass $dataObject): stdClass
    {
        $dataObject = clone $dataObject; // for easier comparison in assertions
        $listener = new VersionListener(new EnvVarProcessor());
        $listener->onConfigLoad(new ParseConfigurationEvent());

        // Simulate loading a configuration item
        $listener->onConfigParse(new ParseResourceEvent($dataObject));
        $listener->onConfigMerge(new MergeResourceEvent($dataObject));

        return $dataObject;
    }
}
