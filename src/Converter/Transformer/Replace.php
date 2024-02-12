<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Transformer;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;

class Replace implements ConverterInterface
{
    private string $search;
    private string $replacement;

    /**
     * @inheritdoc
     */
    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('search', Parameter::TYPE_STRING, true)
            ->addParameter('replacement', Parameter::TYPE_STRING, false, '')
            ->process($parameters);

        $this->search = $input->get('search');
        $this->replacement = $input->get('replacement');
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): string
    {
        $value = (string) $value;

        return $value !== ''
            ? str_replace($this->search, $this->replacement, $value)
            : $value;
    }
}
