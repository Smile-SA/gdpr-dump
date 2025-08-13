<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Traits;

use Faker\Generator;

trait HasFaker
{
    private Generator $faker;

    public function setFaker(Generator $faker): void
    {
        $this->faker = $faker;
    }
}
