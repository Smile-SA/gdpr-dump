<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Generator;

use Random\RandomException;

class RandomDateTime extends RandomDate
{
    protected string $defaultFormat = 'Y-m-d H:i:s';

    /**
     * @inheritdoc
     * @throws RandomException
     */
    protected function randomizeDate(): void
    {
        // Randomize the year, month and day
        parent::randomizeDate();

        // Randomize the hour, minute and second
        $this->date->setTime(
            random_int(0, 23),
            random_int(0, 59),
            random_int(0, 59)
        );
    }
}
