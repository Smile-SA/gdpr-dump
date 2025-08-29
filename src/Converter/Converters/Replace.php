<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Converters;

use Smile\GdprDump\Converter\Converter;
use Smile\GdprDump\Converter\IsConfigurable;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;

final class Replace implements Converter, IsConfigurable
{
    private string $search;
    private string $replacement;

    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('search', Parameter::TYPE_STRING, true)
            ->addParameter('replacement', Parameter::TYPE_STRING, false, '')
            ->process($parameters);

        $this->search = $input->get('search');
        $this->replacement = $input->get('replacement');
    }

    public function convert(mixed $value): string
    {
        $value = (string) $value;

        return $value !== ''
            ? str_replace($this->search, $this->replacement, $value)
            : $value;
    }
}
