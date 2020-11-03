<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Proxy\Cache;
use Smile\GdprDump\Converter\Randomizer\RandomizeText;
use Smile\GdprDump\Tests\Unit\TestCase;
use stdClass;

class CacheTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
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
    public function testConverterNotSet(): void
    {
        $this->expectException(ValidationException::class);
        new Cache(['cache_key' => 'username']);
    }

    /**
     * Assert that an exception is thrown when the parameter "converter" is not an instance of ConverterInterface.
     */
    public function testConverterNotValid(): void
    {
        $this->expectException(ValidationException::class);
        new Cache(['converter' => new stdClass()]);
    }

    /**
     * Assert that an exception is thrown when the parameter "cache_key" is not set.
     */
    public function testCacheKeyNotSet(): void
    {
        $this->expectException(ValidationException::class);
        new Cache(['converter' => new RandomizeText()]);
    }

    /**
     * Assert that an exception is thrown when the parameter "cache_key" is empty.
     */
    public function testEmptyCacheKey(): void
    {
        $this->expectException(ValidationException::class);
        new Cache(['converter' => new RandomizeText(), 'cache_key' => '']);
    }
}
