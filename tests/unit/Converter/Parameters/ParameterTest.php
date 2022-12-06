<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Parameters;

use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Tests\Unit\TestCase;

class ParameterTest extends TestCase
{
    /**
     * Test the parameter object.
     */
    public function testParameter(): void
    {
        // Default constructor
        $parameter = new Parameter('param', Parameter::TYPE_STRING);
        $this->assertSame('param', $parameter->getName());
        $this->assertFalse($parameter->isRequired());
        $this->assertNull($parameter->getDefault());

        // Required parameter
        $parameter = new Parameter('param', Parameter::TYPE_STRING, true);
        $this->assertTrue($parameter->isRequired());

        // Default value
        $parameter = new Parameter('param', Parameter::TYPE_STRING, false, 'default');
        $this->assertSame('default', $parameter->getDefault());
    }

    /**
     * Test the parameter types.
     */
    public function testTypes(): void
    {
        // Scalar types
        foreach ([Parameter::TYPE_BOOL, Parameter::TYPE_STRING, Parameter::TYPE_INT, Parameter::TYPE_FLOAT] as $type) {
            $parameter = new Parameter('param', $type);
            $this->assertSame($type, $parameter->getType());
            $this->assertTrue($parameter->isScalar());
            $this->assertFalse($parameter->isArray());
            $this->assertFalse($parameter->isObject());
        }

        // Array type
        $parameter = new Parameter('param', Parameter::TYPE_ARRAY);
        $this->assertSame(Parameter::TYPE_ARRAY, $parameter->getType());
        $this->assertFalse($parameter->isScalar());
        $this->assertTrue($parameter->isArray());
        $this->assertFalse($parameter->isObject());

        // Object type
        $parameter = new Parameter('param', 'className');
        $this->assertSame('className', $parameter->getType());
        $this->assertFalse($parameter->isScalar());
        $this->assertFalse($parameter->isArray());
        $this->assertTrue($parameter->isObject());
    }
}
