<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Generator;

use DateTime;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

class RandomDate implements ConverterInterface
{
    protected string $defaultFormat = 'Y-m-d';
    protected DateTime $date;
    private string $format;
    private int $minYear;
    private int $maxYear;

    public function __construct()
    {
        $this->date = new DateTime();
    }

    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('format', Parameter::TYPE_STRING, true, $this->defaultFormat)
            ->addParameter('min_year', Parameter::TYPE_INT, false, 1900)
            ->addParameter('max_year', Parameter::TYPE_INT)
            ->process($parameters);

        $this->format = $input->get('format');
        $this->minYear = $input->get('min_year') ?? (int) $this->date->format('Y');
        $this->maxYear = $input->get('max_year') ?? (int) $this->date->format('Y');

        if ($this->minYear > $this->maxYear) {
            throw new ValidationException('The parameter "min_year" must be lower than the parameter "max_year".');
        }
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): string
    {
        $this->randomizeDate();

        return $this->date->format($this->format);
    }

    /**
     * Randomize the date.
     */
    protected function randomizeDate(): void
    {
        // Randomize the year, month and day
        $this->date->setDate(
            mt_rand($this->minYear, $this->maxYear),
            mt_rand(1, 12),
            mt_rand(1, 31)
        );
    }
}
