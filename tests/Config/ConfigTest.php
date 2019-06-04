<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Config;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Tests\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var array
     */
    private $data = [
        'string' => 'value',
        'array' => [1, 2],
        'object' => [
            'key' => 'value',
        ],
    ];

    /**
     * Test the constructor.
     */
    public function testConstructor()
    {
        $config = new Config($this->data);
        $this->assertSame($this->data, $config->toArray());
    }

    /**
     * Test the "set", "get" and "has" methods.
     */
    public function testSetValue()
    {
        $config = new Config();
        $config->set('key', 'value');

        $this->assertTrue($config->has('key'));
        $this->assertSame('value', $config->get('key'));
        $this->assertSame(['key' => 'value'], $config->toArray());
    }

    /**
     * Test the "merge" method.
     */
    public function testMerge()
    {
        $config = new Config();
        $config->merge($this->data);
        $config->merge(['array' => [2, 3]]);
        $config->merge(['object' => ['key' => 'new value']]);

        // Assert that string keys are properly merged
        $this->assertSame(['key' => 'new value'], $config->get('object'));

        // Assert that numeric keys are replaced
        $this->assertSame([2, 3], $config->get('array'));
    }

    /**
     * Test the behavior of the "get" method when the specified key is not defined.
     */
    public function testValueNotFound()
    {
        $config = new Config($this->data);

        $this->assertNull($config->get('not.exists'));
        $this->assertSame('defaultValue', $config->get('not.exists', 'defaultValue'));
    }
}
