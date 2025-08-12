<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\EventListener;

use DateTime;
use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\Event\LoadedEvent;
use Smile\GdprDump\Config\EventListener\DumpOutputListener;
use Smile\GdprDump\Config\Validator\ValidationException;
use Smile\GdprDump\Tests\Unit\TestCase;

final class DumpOutputListenerTest extends TestCase
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

        $listener = new DumpOutputListener();
        $listener(new LoadedEvent($config));

        $dumpSettings = $config->get('dump');
        $this->assertIsArray($dumpSettings);
        $this->assertArrayHasKey('output', $dumpSettings);
        $this->assertSame('dump-' . (new DateTime())->format('Ymd') . '.sql', $dumpSettings['output']);
    }

    /**
     * Assert that an exception is thrown when the `dump` parameter has an invalid type.
     */
    public function testInvalidDatabaseType(): void
    {
        $config = new Config(['dump' => 'not an array']);
        $listener = new DumpOutputListener();

        $this->expectException(ValidationException::class);
        $listener(new LoadedEvent($config));
    }
}
