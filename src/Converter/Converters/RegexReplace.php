<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Converters;

use Smile\GdprDump\Converter\Converter;
use Smile\GdprDump\Converter\Exception\ConversionException;
use Smile\GdprDump\Converter\IsConfigurable;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;

final class RegexReplace implements Converter, IsConfigurable
{
    private string $pattern;
    private string $replacement;
    private int $limit;

    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('pattern', Parameter::TYPE_STRING, true)
            ->addParameter('replacement', Parameter::TYPE_STRING, false, '')
            ->addParameter('limit', Parameter::TYPE_INT, true, -1)
            ->process($parameters);

        $this->pattern = $input->get('pattern');
        $this->replacement = $input->get('replacement');
        $this->limit = $input->get('limit');
    }

    public function convert(mixed $value): string
    {
        $value = (string) $value;

        if ($value !== '') {
            $value = preg_replace($this->pattern, $this->replacement, $value, $this->limit);

            if ($value === null) {
                throw new ConversionException(
                    sprintf('Failed to perform a regex search and replace with the pattern "%s".', $this->pattern)
                );
            }
        }

        return $value;
    }
}
