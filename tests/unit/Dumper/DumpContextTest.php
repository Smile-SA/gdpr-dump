<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper;

use Smile\GdprDump\Dumper\DumpContext;
use Smile\GdprDump\Tests\Unit\TestCase;

final class DumpContextTest extends TestCase
{
    /**
     * Test object creation.
     */
    public function testInitialState(): void
    {
        $dumpContext = new DumpContext();
        $this->assertSame([], $dumpContext->currentRow);
        $this->assertSame([], $dumpContext->processedData);
        $this->assertSame([], $dumpContext->variables);
    }
}
