<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Generator;

use DateTime;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

final class RandomDate implements ConverterInterface
{
    private DateTime $date;
    private string $format;
    private int $minYear;
    private int $maxYear;

    /**
     * @throws ValidationException
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
            throw new ValidationException('The parameter "min_year" must be lower than the parameter "max_year".');
        }
    }

    public function convert(mixed $value, array $context = []): string
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
