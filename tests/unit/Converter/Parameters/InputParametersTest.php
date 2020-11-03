<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Parameters;

use Smile\GdprDump\Converter\Parameters\InputParameters;
use Smile\GdprDump\Tests\Unit\TestCase;

class InputParametersTest extends TestCase
{
    /**
     * Test the input parameters object.
     */
    public function testInputParameters(): void
    {
        $input = new InputParameters(['key1' => 'value1']);

        $this->assertTrue($input->has('key1'));
        $this->assertFalse($input->has('key2'));
        $this->assertSame('value1', $input->get('key1'));
        $this->assertNull($input->get('value2'));
    }
}
