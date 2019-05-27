<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Converter\Randomizer;

use Smile\Anonymizer\Converter\ConverterInterface;

class RandomizeDate implements ConverterInterface
{
    /**
     * @var string
     */
    protected $format = 'Y-m-d';

    /**
     * @var int
     */
    protected $minYear = 1900;

    /**
     * @var int
     */
    protected $maxYear;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->date = new \DateTime();

        if (isset($parameters['format'])) {
            $this->format = (string) $parameters['format'];
        }

        // Min year is the current year if the parameter is set to null, 1900 if the parameter is not defined
        if (array_key_exists('min_year', $parameters)) {
            $this->minYear = (int) ($parameters['min_year'] !== null ? $parameters['min_year'] : $this->date->format('Y'));
        }

        // Max year is the current year if the parameter is not defined or set to null
        $this->maxYear = (int) ($parameters['max_year'] ?? $this->date->format('Y'));

        if ($this->minYear > $this->maxYear) {
            throw new \UnexpectedValueException('The minYear parameter must be lower than the maxYear parameter.');
        }
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $this->randomizeDate();

        return $this->date->format($this->format);
    }

    /**
     * Randomize the date.
     */
    protected function randomizeDate()
    {
        // Randomize the year, month and day
        $day = mt_rand(1, 31);
        $month = mt_rand(1, 12);
        $year = mt_rand($this->minYear, $this->maxYear);

        // Replace the values
        $this->date->setDate($year, $month, $day);
    }
}
