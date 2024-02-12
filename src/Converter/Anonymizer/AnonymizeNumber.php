<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Anonymizer;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;

class AnonymizeNumber implements ConverterInterface
{
    private string $replacement;
    private int $minNumberLength;
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
            ->addParameter('replacement', Parameter::TYPE_STRING, true, '*')
            ->addParameter('min_number_length', Parameter::TYPE_INT, true, 1)
            ->process($parameters);

        $this->replacement = $input->get('replacement');
        $this->minNumberLength = $input->get('min_number_length');
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
        $currentNumberLength = 0;
        $array = $this->multiByteEnabled ? mb_str_split($value, 1, 'UTF-8') : str_split($value);
        $lastKey = null;

        foreach ($array as $index => $char) {
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
            if ($currentNumberLength < $this->minNumberLength) {
                if ($lastKey === null) {
                    // Calculate the last key only once and when needed
                    $lastKey = array_key_last($array);
                }

                if ($index === $lastKey || !is_numeric($array[$index + 1])) {
                    $multiplier = $this->minNumberLength - $currentNumberLength;
                    $result .= str_repeat($this->replacement, $multiplier);
                }
            }
        }

        return $result;
    }
}
