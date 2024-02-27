<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Randomizer;

use Random\RandomException;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

class RandomizeText implements ConverterInterface
{
    private int $minLength;
    private string $replacements;
    private int $replacementsCount;

    /**
     * @inheritdoc
     * @throws ValidationException
     */
    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('replacements', Parameter::TYPE_STRING, true, '0123456789abcdefghijklmnopqrstuvwxyz')
            ->addParameter('min_length', Parameter::TYPE_INT, true, 3)
            ->process($parameters);

        $this->minLength = $input->get('min_length');
        $this->replacements = $input->get('replacements');
        $this->replacementsCount = strlen($this->replacements);
    }

    /**
     * @inheritdoc
     * @throws RandomException
     */
    public function convert(mixed $value, array $context = []): string
    {
        $value = (string) $value;
        if ($value === '') {
            return $value;
        }

        $result = '';
        $length = strlen($value);

        if ($length < $this->minLength) {
            $length = $this->minLength;
        }

        for ($index = 0; $index < $length; $index++) {
            $replacementIndex = random_int(0, $this->replacementsCount - 1);
            $result .= $this->replacements[$replacementIndex];
        }

        return $result;
    }
}
