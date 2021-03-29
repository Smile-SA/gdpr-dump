<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Transformer;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

class Replace implements ConverterInterface
{
    /**
     * @var string
     */
    private $search;

    /**
     * @var string
     */
    private $replacement;

    /**
     * @param array $parameters
     * @throws ValidationException
     */
    public function __construct(array $parameters)
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
    public function convert($value, array $context = [])
    {
        $value = (string) $value;

        return $value !== ''
            ? str_replace($this->search, $this->replacement, (string) $value)
            : $value;
    }
}
