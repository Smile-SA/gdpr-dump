<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Base;

use Smile\GdprDump\Converter\Base\SetValue;
use Smile\GdprDump\Tests\Unit\TestCase;

class SetValueTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $parameters = [
            'value' => 1,
        ];

        $converter = new SetValue($parameters);

        $value = $converter->convert('notAnonymized');
        $this->assertSame(1, $value);
    }

    /**
     * Assert that an exception is thrown when the value is not set.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testValueNotSet()
    {
        new SetValue([]);
    }
}
