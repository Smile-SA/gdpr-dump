<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Anonymizer;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;

class AnonymizeText implements ConverterInterface
{
    /**
     * @var string[]
     */
    private array $delimiters;

    private string $replacement;
    private int $minWordLength;
    private bool $multiByteEnabled;

    public function __construct()
    {
        // Call the extension_loaded function only once (few seconds gain when converting millions of values)
        $this->multiByteEnabled = extension_loaded('mbstring');
    }

    /**
     * @inheritdoc
     */
    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('delimiters', Parameter::TYPE_ARRAY, false, [' ', '_', '-', '.'])
            ->addParameter('replacement', Parameter::TYPE_STRING, true, '*')
            ->addParameter('min_word_length', Parameter::TYPE_INT, true, 3)
            ->process($parameters);

        $this->delimiters = $input->get('delimiters');
        $this->replacement = $input->get('replacement');
        $this->minWordLength = $input->get('min_word_length');

        // Flip separators array for increased performance
        $this->delimiters = array_flip($this->delimiters);
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): string
    {
        $value = (string) $value;
        if ($value === '') {
            return $value;
        }

        $result = '';
        $currentWordLength = 0;
        $array = $this->multiByteEnabled ? mb_str_split($value, 1, 'UTF-8') : str_split($value);
        $lastKey = null;

        foreach ($array as $index => $char) {
            // Preserve separator characters (using isset instead of array_key_exists because it's faster)
            if (isset($this->delimiters[$char])) {
                $result .= $char;
                $currentWordLength = 0;
                continue;
            }

            // Add the replacement character (unless it is the first character of the word) and increase counters
            $result .= $currentWordLength === 0 ? $char : $this->replacement;
            $currentWordLength++;

            // Make sure the generated word has the minimum expected size
            if ($currentWordLength < $this->minWordLength) {
                if ($lastKey === null) {
                    // Calculate the last key only once and when needed
                    $lastKey = array_key_last($array);
                }

                if ($index === $lastKey || isset($this->delimiters[$array[$index + 1]])) {
                    $multiplier = $this->minWordLength - $currentWordLength;
                    $result .= str_repeat($this->replacement, $multiplier);
                }
            }
        }

        return $result;
    }
}
