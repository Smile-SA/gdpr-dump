<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Compiler\Processor;

use DateTime;
use Smile\GdprDump\Configuration\Compiler\Processor\DumpOutputProcessor;
use Smile\GdprDump\Configuration\Loader\Container;
use Smile\GdprDump\Tests\Unit\TestCase;

final class DumpOutputProcessorTest extends TestCase
{
    /**
     * Assert that date placeholders are processed.
     */
    public function testDatePlaceholder(): void
    {
        $container = new Container(
            (object) [
                'dump' => (object) [
                    'output' => 'dump-{Ymd}.sql',
                ],
            ]
        );

        $processor = new DumpOutputProcessor();
        $processor->process($container);

        $dump = $container->get('dump');
        $this->assertObjectHasProperty('output', $dump);
        $this->assertSame('dump-' . (new DateTime())->format('Ymd') . '.sql', $dump->output);
    }
}
