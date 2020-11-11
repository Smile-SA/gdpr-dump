<?php

declare(strict_types=1);

namespace Smile\GdprDump\Faker;

use Faker\Factory;
use Faker\Generator;

class FakerService
{
    /**
     * @var Generator|null
     */
    private $generator;

    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options + [
            'locale' => Factory::DEFAULT_LOCALE,
        ];
    }

    /**
     * Get the Faker generator.
     *
     * @return Generator
     */
    public function getGenerator(): Generator
    {
        if ($this->generator === null) {
            $this->generator = Factory::create($this->options['locale']);
        }

        return $this->generator;
    }
}
