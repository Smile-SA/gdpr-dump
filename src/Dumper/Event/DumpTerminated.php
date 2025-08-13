<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event that is always dispatched at the end of the dump function, even if the dump failed or didn't happen.
 */
final class DumpTerminated extends Event
{
}
