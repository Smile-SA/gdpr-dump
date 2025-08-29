<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use Faker\Generator;

interface IsFakerAware
{
    /**
     * Set the faker provider.
     */
    public function setFaker(Generator $faker): void;
}
