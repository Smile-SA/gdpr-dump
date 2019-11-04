<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use Smile\GdprDump\Converter\Proxy\Cache;
use Smile\GdprDump\Converter\Randomizer\RandomizeText;
use Smile\GdprDump\Tests\Unit\TestCase;

class CacheTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter1 = new Cache(['converter' => new RandomizeText()]);
        $converter2 = new Cache(['cache_key' => 'cache2', 'converter' => new RandomizeText()]);

        $value = 'textToAnonymize';
        $value1 = $converter1->convert($value);
        $value2 = $converter1->convert($value);
        $this->assertSame($value1, $value2);

        $value3 = $converter2->convert($value);
        $this->assertNotSame($value3, $value2);
    }

    /**
     * Assert that an exception is thrown when the converter cache is not set.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testConverterNotSet()
    {
        new Cache([]);
    }
}
