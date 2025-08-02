<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy\Internal;

use Smile\GdprDump\Converter\ConditionBuilder;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\InternalConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;

final class Conditional implements InternalConverterInterface
{
    private string $condition;
    private ConverterInterface $converter;

    public function __construct(private ConditionBuilder $conditionBuilder)
    {
    }

    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('condition', Parameter::TYPE_STRING, true)
            ->addParameter('converter', ConverterInterface::class, true)
            ->process($parameters);

        $this->condition = $this->conditionBuilder->build($input->get('condition'));
        $this->converter = $input->get('converter');
    }

    public function convert(mixed $value, array $context = []): mixed
    {
        $result = (bool) eval($this->condition);

        return $result ? $this->converter->convert($value, $context) : $value;
    }
}
