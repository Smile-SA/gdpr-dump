<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Randomizer;

use Smile\GdprDump\Converter\ConverterInterface;

class RandomizeText implements ConverterInterface
{
    /**
     * @var int
     */
    private $minLength = 3;

    /**
     * @var string
     */
    private $replacements = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    /**
     * @var int
     */
    private $replacementsCount;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        if (isset($parameters['min_length'])) {
            $this->minLength = (int) $parameters['min_length'];
        }

        if (isset($parameters['replacements'])) {
            $this->replacements = (string) $parameters['replacements'];
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
