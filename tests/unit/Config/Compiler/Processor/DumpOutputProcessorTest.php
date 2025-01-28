<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Compiler\Processor;

use DateTime;
use Smile\GdprDump\Config\Compiler\Processor\DumpOutputProcessor;
use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Tests\Unit\TestCase;

final class DumpOutputProcessorTest extends TestCase
{
    /**
     * Assert that date placeholders are processed.
     */
    public function testDatePlaceholder(): void
    {
        $config = new Config([
            'dump' => [
                'output' => 'dump-{Ymd}.sql',
            ],
        ]);

        $processor = new DumpOutputProcessor();
        $processor->process($config);

        $dumpSettings = $config->get('dump');
        $this->assertIsArray($dumpSettings);
        $this->assertArrayHasKey('output', $dumpSettings);
        $this->assertSame('dump-' . (new DateTime())->format('Ymd') . '.sql', $dumpSettings['output']);
    }
}
