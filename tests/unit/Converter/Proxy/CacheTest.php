<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use InvalidArgumentException;
use Smile\GdprDump\Converter\Proxy\Cache;
use Smile\GdprDump\Converter\Randomizer\RandomizeText;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

class CacheTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter1 = new Cache(['converter' => new RandomizeText(), 'cache_key' => 'key1']);
        $converter2 = new Cache(['converter' => new RandomizeText(), 'cache_key' => 'key2']);

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
    public function testConverterNotSet()
    {
        $this->expectException(InvalidArgumentException::class);
        new Cache(['cache_key' => 'username']);
    }

    /**
     * Assert that an exception is thrown when the parameter "cache_key" is not set.
     */
    public function testCacheKeyNotSet()
    {
        $this->expectException(InvalidArgumentException::class);
        new Cache(['converter' => new RandomizeText()]);
    }

    /**
     * Assert that an exception is thrown when the parameter "cache_key" is empty.
     */
    public function testEmptyCacheKey()
    {
        $this->expectException(UnexpectedValueException::class);
        new Cache(['converter' => new RandomizeText(), 'cache_key' => '']);
    }
}
