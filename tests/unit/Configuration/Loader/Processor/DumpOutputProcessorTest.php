<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Loader\Processor;

use DateTime;
use Smile\GdprDump\Configuration\Loader\Processor\DumpOutputProcessor;
use Smile\GdprDump\Tests\Unit\TestCase;

final class DumpOutputProcessorTest extends TestCase
{
    /**
     * Assert that date placeholders are processed.
     */
    public function testDatePlaceholder(): void
    {
        $data = (object) [
            'dump' => (object) [
                'output' => 'dump-{Ymd}.sql',
            ],
        ];

        $processor = new DumpOutputProcessor();
        $processor->process($data);

        $this->assertSame('dump-' . (new DateTime())->format('Ymd') . '.sql', $data->dump->output);
    }

    /**
     * Assert that the processor performs no action when the dump object is invalid.
     */
    public function testInvalidDumpType(): void
    {
        $data = (object) ['dump' => 'not an object'];

        $processor = new DumpOutputProcessor();
        $clonedData = clone $data;
        $processor->process($data);
        $this->assertEquals($data, $clonedData);
    }

    /**
     * Assert that the processor performs no action when the dump output is invalid.
     */
    public function testInvalidOutputType(): void
    {
        $data = (object) ['dump' => (object) ['output' => []]];

        $processor = new DumpOutputProcessor();
        $clonedData = clone $data;
        $processor->process($data);
        $this->assertEquals($data, $clonedData);
    }
}
