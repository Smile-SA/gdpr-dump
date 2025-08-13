<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\EventListener;

use DateTime;
use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Configuration\Event\ConfigurationParsedEvent;
use Smile\GdprDump\Configuration\Event\LoadedEvent;
use Smile\GdprDump\Configuration\EventListener\DumpOutputListener;
use Smile\GdprDump\Configuration\Exception\ConfigLoadException;
use Smile\GdprDump\Configuration\Validator\ValidationException;
use Smile\GdprDump\Tests\Unit\TestCase;

final class DumpOutputListenerTest extends TestCase
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

        $listener = new DumpOutputListener();
        $listener(new ConfigurationParsedEvent($data));

        $this->assertSame('dump-' . (new DateTime())->format('Ymd') . '.sql', $data->dump->output);
    }

    /**
     * Assert that an exception is thrown when the `dump` parameter has an invalid type.
     */
    public function testInvalidOutputType(): void
    {
        $listener = new DumpOutputListener();
        $this->expectException(ConfigLoadException::class);
        $listener(new ConfigurationParsedEvent((object) ['dump' => 'not an object']));
    }
}
