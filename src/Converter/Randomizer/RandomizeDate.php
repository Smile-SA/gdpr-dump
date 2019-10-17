<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Randomizer;

use DateTime;
use Smile\GdprDump\Converter\ConverterInterface;
use UnexpectedValueException;

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
     * @var DateTime
     */
    protected $date;

    /**
     * @param array $parameters
     * @throws UnexpectedValueException
     */
    public function __construct(array $parameters = [])
    {
        $this->date = new DateTime();

        if (isset($parameters['format'])) {
            $this->format = (string) $parameters['format'];
        }

        // Min year is the current year if the parameter is set to null, 1900 if the parameter is not defined
        if (array_key_exists('min_year', $parameters)) {
            $this->minYear = (int) ($parameters['min_year'] !== null
                ? $parameters['min_year']
                : $this->date->format('Y'));
        }

        // Max year is the current year if the parameter is not defined or set to null
        $this->maxYear = (int) ($parameters['max_year'] ?? $this->date->format('Y'));

        if ($this->minYear > $this->maxYear) {
            throw new UnexpectedValueException('The min_year parameter must be lower than the max_year parameter.');
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
