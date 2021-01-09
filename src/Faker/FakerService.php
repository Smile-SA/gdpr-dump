<?php

declare(strict_types=1);

namespace Smile\GdprDump\Faker;

use Faker\Factory;
use Faker\Generator;
use Smile\GdprDump\Config\ConfigInterface;

class FakerService
{
    /**
     * @var Generator|null
     */
    private $generator;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Get the Faker generator.
     *
     * @return Generator
     */
    public function getGenerator(): Generator
    {
        if ($this->generator === null) {
            $locale = $this->config->get('faker', [])['locale'] ?? Factory::DEFAULT_LOCALE;
            $this->generator = Factory::create($locale);
        }

        return $this->generator;
    }
}
