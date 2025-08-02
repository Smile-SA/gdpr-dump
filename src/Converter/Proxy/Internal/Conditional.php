<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy\Internal;

use Smile\GdprDump\Converter\ConditionBuilder;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\InternalConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Dumper\DumpContext;

final class Conditional implements InternalConverterInterface
{
    private string $condition;
    private ConverterInterface $converter;

    public function __construct(private ConditionBuilder $conditionBuilder, private DumpContext $dumpContext)
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

    /**
     * @see ConditionBuilder::parseCondition()
     */
    public function convert(mixed $value): mixed
    {
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $dumpContext = $this->dumpContext; // necessary for the condition to work
        $result = (bool) eval($this->condition);

        return $result ? $this->converter->convert($value) : $value;
    }
}
