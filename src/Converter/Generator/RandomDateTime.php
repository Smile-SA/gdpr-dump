<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Generator;

final class RandomDateTime extends RandomDate
{
    protected string $defaultFormat = 'Y-m-d H:i:s';

    /**
     * @inheritdoc
     */
    protected function randomizeDate(): void
    {
        // Randomize the year, month and day
        parent::randomizeDate();

        // Randomize the hour, minute and second
        $this->date->setTime(
            mt_rand(0, 23),
            mt_rand(0, 59),
            mt_rand(0, 59)
        );
    }
}
