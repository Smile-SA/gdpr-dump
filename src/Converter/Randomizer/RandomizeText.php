<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Randomizer;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

class RandomizeText implements ConverterInterface
{
    /**
     * @var int
     */
    private $minLength;

    /**
     * @var string
     */
    private $replacements;

    /**
     * @var int
     */
    private $replacementsCount;

    /**
     * @param array $parameters
     * @throws ValidationException
     */
    public function __construct(array $parameters = [])
    {
        $input = (new ParameterProcessor())
            ->addParameter('replacements', Parameter::TYPE_STRING, true, '0123456789abcdefghijklmnopqrstuvwxyz')
            ->addParameter('min_length', Parameter::TYPE_STRING, true, 3)
            ->process($parameters);

        $this->replacements = $input->get('replacements');
        $this->minLength = $input->get('min_length');
        $this->replacementsCount = strlen($this->replacements);
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
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
            $replacementIndex = mt_rand(0, $this->replacementsCount - 1);
            $result .=  $this->replacements[$replacementIndex];
        }

        return $result;
    }
}
