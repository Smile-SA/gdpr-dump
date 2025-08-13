<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy\Internal;

use Smile\GdprDump\Converter\ConditionBuilder;
use Smile\GdprDump\Converter\IsContextAware;
use Smile\GdprDump\Converter\Converter;
use Smile\GdprDump\Converter\IsInternal;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Dumper\DumpContext;

final class Conditional implements IsInternal, IsContextAware
{
    private DumpContext $dumpContext; // @phpstan-ignore property.onlyWritten (required for condition evaluation)
    private string $condition;
    private Converter $converter;

    public function __construct(private ConditionBuilder $conditionBuilder)
    {
    }

    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('condition', Parameter::TYPE_STRING, true)
            ->addParameter('converter', Converter::class, true)
            ->process($parameters);

        $this->condition = $this->conditionBuilder->build($input->get('condition'));
        $this->converter = $input->get('converter');
    }

    public function setDumpContext(DumpContext $dumpContext): void
    {
        $this->dumpContext = $dumpContext;
    }

    public function convert(mixed $value): mixed
    {
        $result = (bool) eval($this->condition);

        return $result ? $this->converter->convert($value) : $value;
    }
}
