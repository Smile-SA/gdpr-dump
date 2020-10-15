<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Anonymizer;

use Smile\GdprDump\Converter\ConverterInterface;
use UnexpectedValueException;

class AnonymizeText implements ConverterInterface
{
    /**
     * @var string[]
     */
    private $delimiters = [' ', '_', '.'];

    /**
     * @var string
     */
    private $replacement = '*';

    /**
     * @var int
     */
    private $minWordLength = 1;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        if (array_key_exists('delimiters', $parameters)) {
            if (!is_array($parameters['delimiters'])) {
                throw new UnexpectedValueException('The parameter "delimiters" must be an array.');
            }

            $this->delimiters = $parameters['delimiters'];
        }

        if (array_key_exists('replacement', $parameters)) {
            $this->replacement = (string) $parameters['replacement'];
        }

        if (array_key_exists('min_word_length', $parameters)) {
            $this->minWordLength = (int) $parameters['min_word_length'];
        }

        // Flip separators array for increased performance
        $this->delimiters = array_flip($this->delimiters);
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $result = '';
        $currentWordLength = 0;
        $value = mb_str_split((string) $value);
        $lastKey = array_key_last($value);

        foreach ($value as $index => $char) {
            // Preserve separator characters
            if (array_key_exists($char, $this->delimiters)) {
                $result .= $char;
                $currentWordLength = 0;
                continue;
            }

            // Add the replacement character (unless it is the first character of the word) and increase counters
            $result .= $currentWordLength === 0 ? $char : $this->replacement;
            $currentWordLength++;

            // Make sure the generated word has the minimum expected size
            $checkWordLength = $index === $lastKey || array_key_exists($value[$index + 1], $this->delimiters);
            if ($checkWordLength && $currentWordLength < $this->minWordLength) {
                $multiplier = $this->minWordLength - $currentWordLength;
                $result .= str_repeat($this->replacement, $multiplier);
            }
        }

        return $result;
    }
}
