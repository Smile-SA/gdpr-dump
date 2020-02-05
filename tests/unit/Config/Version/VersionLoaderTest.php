<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Version;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\Version\VersionLoader;
use Smile\GdprDump\Tests\Unit\TestCase;

class VersionLoaderTest extends TestCase
{
    /**
     * Test the config version loader.
     */
    public function testConfigLoader()
    {
        $configLoader = new VersionLoader();
        $data = [
            'requires_version' => true,
            'if_version' => [
                '>=1.0.0 <2.0.0' => [
                    'key' => 'new_value',
                ],
                '<1.1.0' => [
                    'key' => 'old_value',
                ],
            ],
        ];

        $config = new Config($data);
        $config->set('version', '1.1.0');
        $configLoader->load($config);
        $this->assertSame('new_value', $config->get('key'));

        $config = new Config($data);
        $config->set('version', '1.0.18');
        $configLoader->load($config);
        $this->assertSame('old_value', $config->get('key'));

        $config = new Config($data);
        $config->set('version', '2.0.0');
        $configLoader->load($config);
        $this->assertFalse($config->has('key'));
    }

    /**
     * Assert that an exception is thrown when the version was not specified.
     *
     * @expectedException \Smile\GdprDump\Config\Version\MissingVersionException
     */
    public function testVersionNotSpecifiedException()
    {
        $config = new Config(['requires_version' => true]);
        $configLoader = new VersionLoader();
        $configLoader->load($config);
    }
}
