<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Listener;

use Smile\GdprDump\Dumper\Event\DumpEvent;
use Smile\GdprDump\Faker\FakerService;

class FakerLocaleListener
{
    public function __construct(private FakerService $fakerService)
    {
    }

    /**
     * Apply the faker locale specified in the config file.
     */
    public function __invoke(DumpEvent $event): void
    {
        $locale = $event->getConfig()->getFakerSettings()->getLocale();
        if ($locale !== '') {
            $this->fakerService->setLocale($locale);
        }
    }
}
