<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Listener;

use Smile\GdprDump\Dumper\Event\DumpEvent;
use Smile\GdprDump\Faker\FakerService;

class FakerLocaleListener
{
    public function __construct(private FakerService $faker)
    {
    }

    /**
     * Apply the faker locale specified in the config file.
     */
    public function __invoke(DumpEvent $event): void
    {
        $locale = (string) ($event->getConfig()->getFakerSettings()['locale'] ?? '');
        if ($locale !== '') {
            $this->faker->setLocale($locale);
        }
    }
}
