<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Parameters;

use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Tests\Unit\TestCase;
use stdClass;

class ParameterProcessorTest extends TestCase
{
    public function testProcessor(): void
    {
        $processor = new ParameterProcessor();
        $processor->addParameter('string', Parameter::TYPE_STRING, true, 'default');
        $processor->addParameter('array', Parameter::TYPE_ARRAY);
        $processor->addParameter('object', stdClass::class, true);

        $values = [
            'object' => new stdClass(),
        ];

        $input = $processor->process($values);
        $this->assertSame('default', $input->get('string'));
        $this->assertNull($input->get('array'));
        $this->assertInstanceOf(stdClass::class, $input->get('object'));

        $values = [
            'string' => 'value',
            'array' => [],
            'object' => new stdClass(),
        ];

        $input = $processor->process($values);
        $this->assertSame('value', $input->get('string'));
        $this->assertSame([], $input->get('array'));
        $this->assertInstanceOf(stdClass::class, $input->get('object'));

        $this->expectException(ValidationException::class);
        $processor->process([]);
    }
}
