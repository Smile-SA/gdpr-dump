<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Randomizer;

class RandomizeDateTime extends RandomizeDate
{
    /**
     * @inheritdoc
     */
    protected $format = 'Y-m-d H:i:s';

    /**
     * @inheritdoc
     */
    protected function randomizeDate()
    {
        // Randomize the year, month and day
        parent::randomizeDate();

        // Randomize the hour, minute and second
        $hour = mt_rand(0, 23);
        $minute = mt_rand(0, 59);
        $second = mt_rand(0, 59);

        // Replace the values
        $this->date->setTime($hour, $minute, $second);
    }
}
