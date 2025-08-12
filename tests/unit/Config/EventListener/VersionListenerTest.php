<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\EventListener;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\Event\LoadEvent;
use Smile\GdprDump\Config\Event\MergeEvent;
use Smile\GdprDump\Config\Event\ParseEvent;
use Smile\GdprDump\Config\EventListener\VersionListener;
use Smile\GdprDump\Config\Version\MissingVersionException;
use Smile\GdprDump\Tests\Unit\TestCase;

final class VersionListenerTest extends TestCase
{
    /**
     * Assert that "if_version" blocks are processed successfully.
     */
    public function testVersionListener(): void
    {
        $data = [
            'items' => ['key' => 'value'],
            'if_version' => [
                '>=1.0.0 <2.0.0' => [
                    'items' => ['key' => 'new_value_1'],
                    'new_key' => 1,
                ],
                '>=2.0.0' => [
                    'items' => ['key' => 'new_value_2'],
                ],
            ],
        ];

        // Test with version "1.0.0"
        $data['version'] = '1.0.0';
        $config = $this->processVersions($data);
        //$this->assertSame('1.0.0', $config->get('version'));
        $this->assertSame(['key' => 'new_value_1'], $config->get('items'));
        $this->assertTrue($config->has('new_key'));

        // Test with version "2.0.0"
        $data['version'] = '2.0.0';
        $config = $this->processVersions($data);
        //$this->assertSame('2.0.0', $config->get('version'));
        $this->assertSame(['key' => 'new_value_2'], $config->get('items'));
        $this->assertFalse($config->has('new_key'));

        // Test with version "0.9.0" set in the configuration
        unset($data['version']);
        $config = $this->processVersions($data, new Config(['version' => '0.9.0']));
        //$this->assertSame('0.9.0', $config->get('version'));
        $this->assertSame(['key' => 'value'], $config->get('items'));
        $this->assertFalse($config->has('new_key'));
    }

    /**
     * Assert that an exception is thrown when the version was not specified.
     */
    public function testVersionNotSpecifiedException(): void
    {
        $this->expectException(MissingVersionException::class);
        $this->processVersions(['if_version' => ['>=2.0.0' => ['key' => 'value']]]);
    }

    /**
     * Create and run the event listener with the specified configuration.
     */
    private function processVersions(array $data, ?Config $config = null): Config
    {

        if (!$config) {
            $config = new Config();
        }

        $listener = new VersionListener();
        $listener->onLoad(new LoadEvent($config));

        // Simulate loading a configuration item
        $dataObject = new Config($data);
        $listener->onParse(new ParseEvent($dataObject));
        $listener->onMerge(new MergeEvent($dataObject));
        $config->merge($dataObject->toArray());

        return $config;
    }
}
