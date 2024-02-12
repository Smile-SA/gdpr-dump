<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Proxy\Cache;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;
use stdClass;

class CacheTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter1 = $this->createConverter(Cache::class, [
            'converter' => new ConverterMock(['prefix' => '1_']),
            'cache_key' => 'key1',
        ]);
        $converter2 = $this->createConverter(Cache::class, [
            'converter' => new ConverterMock(['prefix' => '2_']),
            'cache_key' => 'key2',
        ]);

        $value = 'textToAnonymize';
        $value1 = $converter1->convert($value);
        $value2 = $converter1->convert($value);
        $this->assertSame($value1, $value2);

        $value3 = $converter2->convert($value);
        $this->assertNotSame($value3, $value2);
    }

    /**
     * Assert that an exception is thrown when the parameter "converter" is not set.
     */
    public function testConverterNotSet(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(Cache::class, ['cache_key' => 'username']);
    }

    /**
     * Assert that an exception is thrown when the parameter "converter" is not an instance of ConverterInterface.
     */
    public function testConverterNotValid(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(Cache::class, [
            'converter' => new stdClass(),
            'cache_key' => 'username',
        ]);
    }

    /**
     * Assert that an exception is thrown when the parameter "cache_key" is not set.
     */
    public function testCacheKeyNotSet(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(Cache::class, ['converter' => new ConverterMock()]);
    }

    /**
     * Assert that an exception is thrown when the parameter "cache_key" is empty.
     */
    public function testEmptyCacheKey(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(Cache::class, [
            'converter' => new ConverterMock(),
            'cache_key' => '',
        ]);
    }
}
