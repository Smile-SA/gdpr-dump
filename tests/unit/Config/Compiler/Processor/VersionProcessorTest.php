<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Compiler\Processor;

use Smile\GdprDump\Config\Compiler\Processor\Version\MissingVersionException;
use Smile\GdprDump\Config\Compiler\Processor\VersionProcessor;
use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Tests\Unit\TestCase;

final class VersionProcessorTest extends TestCase
{
    /**
     * Assert that "if_version" blocks are processed successfully.
     */
    public function testVersionProcessor(): void
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

        $processor = new VersionProcessor();
        $config = new Config($data);

        // Test with version "1.0.0"
        $config->set('version', '1.0.0');
        $processor->process($config);

        $this->assertSame('1.0.0', $config->get('version'));
        $this->assertSame(['key' => 'new_value_1'], $config->get('items'));
        $this->assertTrue($config->has('new_key'));

        // Test with version "2.0.0"
        $config->reset($data)->set('version', '2.0.0');
        $processor->process($config);

        $this->assertSame('2.0.0', $config->get('version'));
        $this->assertSame(['key' => 'new_value_2'], $config->get('items'));
        $this->assertFalse($config->has('new_key'));

        // Test with version "0.9.0"
        $config->reset($data)->set('version', '0.9.0');
        $processor->process($config);

        $this->assertSame('0.9.0', $config->get('version'));
        $this->assertSame(['key' => 'value'], $config->get('items'));
        $this->assertFalse($config->has('new_key'));
    }

    /**
     * Assert that an exception is thrown when the version was not specified.
     */
    public function testVersionNotSpecifiedException(): void
    {
        $processor = new VersionProcessor();
        $config = new Config(['requires_version' => true]);

        $this->expectException(MissingVersionException::class);
        $processor->process($config);
    }
}
