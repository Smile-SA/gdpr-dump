<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Anonymizer;

use DateTime;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use UnexpectedValueException;

class AnonymizeDate implements ConverterInterface
{
    protected string $defaultFormat = 'Y-m-d';
    private string $format = 'Y-m-d';

    /**
     * @inheritdoc
     */
    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('format', Parameter::TYPE_STRING, true, $this->defaultFormat)
            ->process($parameters);

        $this->format = $input->get('format');
    }

    /**
     * @inheritdoc
     *
     * @throws UnexpectedValueException
     */
    public function convert(mixed $value, array $context = []): string
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
