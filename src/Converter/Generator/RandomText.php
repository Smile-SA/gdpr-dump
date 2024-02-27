<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Generator;

use Random\RandomException;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

class RandomText implements ConverterInterface
{
    protected string $characters;
    private int $minLength;
    private int $maxLength;
    private int $charactersCount;

    /**
     * @inheritdoc
     * @throws ValidationException
     */
    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('characters', Parameter::TYPE_STRING, true, '0123456789abcdefghijklmnopqrstuvwxyz')
            ->addParameter('min_length', Parameter::TYPE_INT, true, 3)
            ->addParameter('max_length', Parameter::TYPE_INT, true, 16)
            ->process($parameters);

        $this->characters = $input->get('characters');
        $this->minLength = $input->get('min_length');
        $this->maxLength = $input->get('max_length');
        $this->charactersCount = strlen($this->characters);
    }

    /**
     * @inheritdoc
     * @throws RandomException
     */
    public function convert(mixed $value, array $context = []): string
    {
        $result = '';
        $length = random_int($this->minLength, $this->maxLength);

        for ($index = 0; $index < $length; $index++) {
            $characterIndex = random_int(0, $this->charactersCount - 1);
            $result .= $this->characters[$characterIndex];
        }

        return $result;
    }
}
