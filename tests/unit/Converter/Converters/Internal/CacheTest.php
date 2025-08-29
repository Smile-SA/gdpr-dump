<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Converters\Internal;

use Smile\GdprDump\Converter\Converters\Internal\Cache;
use Smile\GdprDump\Converter\Converters\RandomizeText;
use Smile\GdprDump\Converter\Exception\InvalidParameterException;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;
use stdClass;

final class CacheTest extends TestCase
{
    /**
     * Assert that values are properly cached.
     */
    public function testValueIsCached(): void
    {
        $parameters = [
            'converter' => $this->createConverter(RandomizeText::class),
            'cache_key' => 'key1',
        ];

        $converter = $this->createConverter(Cache::class, $parameters);
        $value = 'text to randomize';
        $convertedValue = $converter->convert($value);

        // The converter must always return the same result for a given value
        $this->assertSame($convertedValue, $converter->convert($value));
        $this->assertNotSame($convertedValue, $converter->convert('another text to randomize'));

        // Value generated must not be the same when using another cache key
        $converter->setParameters(array_merge($parameters, ['cache_key' => 'key2']));
        $this->assertNotSame($convertedValue, $converter->convert($value));
    }

    /**
     * Assert that the cache storage is shared between converter instances.
     */
    public function testCacheIsSharedBetweenInstances(): void
    {
        $value = 'text to randomize';
        $parameters = [
            'converter' => $this->createConverter(RandomizeText::class),
            'cache_key' => 'key1',
        ];

        $this->assertSame(
            $this->createConverter(Cache::class, $parameters)->convert($value),
            $this->createConverter(Cache::class, $parameters)->convert($value)
        );
    }

    /**
     * Assert that an exception is thrown when the parameter "converter" is not set.
     */
    public function testConverterNotSet(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(Cache::class, ['cache_key' => 'username']);
    }

    /**
     * Assert that an exception is thrown when the parameter "converter" is not an instance of Converter.
     */
    public function testConverterNotValid(): void
    {
        $this->expectException(InvalidParameterException::class);
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
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(Cache::class, [
            'converter' => $this->createConverter(ConverterMock::class),
        ]);
    }

    /**
     * Assert that an exception is thrown when the parameter "cache_key" is empty.
     */
    public function testEmptyCacheKey(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(Cache::class, [
            'converter' => $this->createConverter(ConverterMock::class),
            'cache_key' => '',
        ]);
    }
}
