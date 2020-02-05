<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Anonymizer;

use DateTime;
use Smile\GdprDump\Converter\ConverterInterface;
use UnexpectedValueException;

class AnonymizeDate implements ConverterInterface
{
    /**
     * @var string
     */
    protected $format = 'Y-m-d';

    /**
     * @param array $parameters
     * @throws UnexpectedValueException
     */
    public function __construct(array $parameters = [])
    {
        if (array_key_exists('format', $parameters)) {
            $this->format = (string) $parameters['format'];

            if ($this->format === '') {
                throw new UnexpectedValueException('The parameter "format" must not be empty.');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $date = DateTime::createFromFormat($this->format, $value);
        if ($date === false) {
            throw new UnexpectedValueException(sprintf('Failed to convert the value "%s" to a date.', $value));
        }

        $this->anonymizeDate($date);

        return $date->format($this->format);
    }

    /**
     * Anonymize a date, by randomizing the day and month.
     *
     * @param DateTime $date
     */
    protected function anonymizeDate(DateTime $date)
    {
        // Get the year, month and day
        $year = (int) $date->format('Y');
        $month = (int) $date->format('n');
        $day = (int) $date->format('j');

        // Randomize the month and day
        do {
            $randomDay = mt_rand(1, 31);
            $randomMonth = mt_rand(1, 12);
        } while ($randomDay === $day && $randomMonth === $month);

        // Replace the values
        $date->setDate($year, $randomMonth, $randomDay);
    }
}
