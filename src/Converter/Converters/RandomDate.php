<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Converters;

use DateTime;
use Smile\GdprDump\Converter\Converter;
use Smile\GdprDump\Converter\Exception\InvalidParameterException;
use Smile\GdprDump\Converter\IsConfigurable;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;

final class RandomDate implements Converter, IsConfigurable
{
    private DateTime $date;
    private string $format;
    private int $minYear;
    private int $maxYear;

    /**
     * @throws InvalidParameterException
     */
    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('format', Parameter::TYPE_STRING, true, 'Y-m-d')
            ->addParameter('min_year', Parameter::TYPE_INT, false, 1900)
            ->addParameter('max_year', Parameter::TYPE_INT)
            ->process($parameters);

        $this->date = new DateTime();
        $this->format = $input->get('format');
        $this->minYear = $input->get('min_year') ?? (int) $this->date->format('Y');
        $this->maxYear = $input->get('max_year') ?? (int) $this->date->format('Y');

        if ($this->minYear > $this->maxYear) {
            throw new InvalidParameterException(
                'The parameter "min_year" must be lower than the parameter "max_year".'
            );
        }
    }

    public function convert(mixed $value): string
    {
        // Randomize the year, month and day
        $this->date->setDate(
            mt_rand($this->minYear, $this->maxYear),
            mt_rand(1, 12),
            mt_rand(1, 31)
        );

        // Randomize the hour, minute and second
        $this->date->setTime(
            mt_rand(0, 23),
            mt_rand(0, 59),
            mt_rand(0, 59)
        );

        return $this->date->format($this->format);
    }
}
