<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Randomizer;

use Smile\GdprDump\Converter\ConverterInterface;
use UnexpectedValueException;

class RandomizeText implements ConverterInterface
{
    /**
     * @var int
     */
    private $minLength = 3;

    /**
     * @var string
     */
    private $replacements = '0123456789abcdefghijklmnopqrstuvwxyz';

    /**
     * @var int
     */
    private $replacementsCount;

    /**
     * @param array $parameters
     * @throws UnexpectedValueException
     */
    public function __construct(array $parameters = [])
    {
        if (array_key_exists('min_length', $parameters)) {
            $this->minLength = (int) $parameters['min_length'];
        }

        if (array_key_exists('replacements', $parameters)) {
            $this->replacements = (string) $parameters['replacements'];

            if ($this->replacements === '') {
                throw new UnexpectedValueException('The parameter "replacements" must not be empty.');
            }
        }

        $this->replacementsCount = strlen($this->replacements);
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $length = strlen((string) $value);
        $value = '';

        if ($length < $this->minLength) {
            $length = $this->minLength;
        }

        for ($index = 0; $index < $length; $index++) {
            $replacementIndex = mt_rand(0, $this->replacementsCount - 1);
            $value .=  $this->replacements[$replacementIndex];
        }

        return $value;
    }
}
