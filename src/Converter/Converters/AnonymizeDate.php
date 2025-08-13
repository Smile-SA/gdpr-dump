<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Converters;

use DateTime;
use Smile\GdprDump\Converter\Converter;
use Smile\GdprDump\Converter\IsConfigurable;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use UnexpectedValueException;

final class AnonymizeDate implements Converter, IsConfigurable
{
    private string $format;

    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('format', Parameter::TYPE_STRING, true, 'Y-m-d')
            ->process($parameters);

        $this->format = $input->get('format');
    }

    /**
     * @throws UnexpectedValueException
     */
    public function convert(mixed $value): string
    {
        $value = (string) $value;
        if ($value === '') {
            return $value;
        }

        $date = DateTime::createFromFormat($this->format, $value);
        if ($date === false) {
            throw new UnexpectedValueException(sprintf('Failed to convert the value "%s" to a date.', $value));
        }

        $this->anonymizeDate($date);

        return $date->format($this->format);
    }

    /**
     * Anonymize the date.
     */
    private function anonymizeDate(DateTime $date): void
    {
        // Get the year, month and day
        $year = (int) $date->format('Y');
        $month = (int) $date->format('n');
        $day = (int) $date->format('j');

        // Randomize the month and day
        do {
            $randomMonth = mt_rand(1, 12);
            $randomDay = mt_rand(1, 31);
        } while ($randomMonth === $month && $randomDay === $day);

        // Replace the values
        $date->setDate($year, $randomMonth, $randomDay);
    }
}
