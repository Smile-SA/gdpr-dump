<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Anonymizer;

use Smile\GdprDump\Converter\ConverterInterface;

class AnonymizeNumber implements ConverterInterface
{
    /**
     * @var string
     */
    private $replacement = '*';

    /**
     * @var int
     */
    private $minNumberLength = 1;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        if (array_key_exists('replacement', $parameters)) {
            $this->replacement = (string) $parameters['replacement'];
        }

        if (array_key_exists('min_number_length', $parameters)) {
            $this->minNumberLength = (int) $parameters['min_number_length'];
        }
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $result = '';
        $currentNumberLength = 0;
        $value = mb_str_split((string) $value);
        $lastKey = array_key_last($value);

        foreach ($value as $index => $char) {
            // Preserve non-numeric characters
            if (!is_numeric($char)) {
                $result .= $char;
                $currentNumberLength = 0;
                continue;
            }

            // Add the replacement character (unless it is the first character of the word) and increase counters
            $result .= $currentNumberLength === 0 ? $char : $this->replacement;
            $currentNumberLength++;

            // Make sure the generated word has the minimum expected size
            $checkNumberLength = $index === $lastKey || !is_numeric($value[$index + 1]);
            if ($checkNumberLength && $currentNumberLength < $this->minNumberLength) {
                $multiplier = $this->minNumberLength - $currentNumberLength;
                $result .= str_repeat($this->replacement, $multiplier);
            }
        }

        return $result;
    }
}
