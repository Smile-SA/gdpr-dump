<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Config\ConfigTest;

use PHPUnit\Framework\TestCase;
use Smile\Anonymizer\Config\Config;

class ConfigTest extends TestCase
{
    private $data = [
        'key' => 'value',
        'nested' => [
            'key' => 'nested value',
        ],
    ];

    public function testValueFound()
    {
        $config = new Config($this->data);

        // Assert that a config item exists
        $this->assertSame(true, $config->has('key'));
        $this->assertSame(true, $config->has('nested.key'));

        // Assert that config items can be fetched by path
        $this->assertSame('value', $config->get('key'));
        $this->assertSame('nested value', $config->get('nested.key'));

        // Assert that the config can be dumped
        $this->assertSame($this->data, $config->toArray());
    }

    public function testValueNotFound()
    {
        $config = new Config($this->data);

        $this->assertSame(null, $config->get('not.exists'));
        $this->assertSame(true, $config->get('not.exists', true));
        $this->assertSame(false, $config->has('not.exists'));
    }
}
