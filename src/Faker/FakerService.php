<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Faker;

use Faker\Generator;
use Faker\Factory;
use Smile\Anonymizer\Faker\Provider\Internet;

class FakerService
{
    /**
     * @var Generator
     */
    private $generator;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options += [
            'locale' => Factory::DEFAULT_LOCALE,
        ];
    }

    /**
     * Get the Faker generator.
     *
     * @return Generator
     */
    public function getGenerator()
    {
        if ($this->generator === null) {
            $this->generator = Factory::create($this->options['locale']);
            $this->generator->addProvider(new Internet($this->generator));
        }

        return $this->generator;
    }
}
