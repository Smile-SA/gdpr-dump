<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Loader\Processor;

use DateTime;
use Smile\GdprDump\Configuration\Exception\ParseException;
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
     * Assert that an exception is thrown when the `dump` parameter has an invalid type.
     */
    public function testInvalidOutputType(): void
    {
        $processor = new DumpOutputProcessor();
        $this->expectException(ParseException::class);
        $processor->process((object) ['dump' => 'not an object']);
    }
}
