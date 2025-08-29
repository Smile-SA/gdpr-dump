<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Converters\Internal;

use Smile\GdprDump\Converter\Condition\Condition;
use Smile\GdprDump\Converter\Condition\ConditionBuilder;
use Smile\GdprDump\Converter\Converter;
use Smile\GdprDump\Converter\IsConfigurable;
use Smile\GdprDump\Converter\IsContextAware;
use Smile\GdprDump\Converter\IsInternal;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Traits\HasDumpContext;

final class Conditional implements Converter, IsConfigurable, IsContextAware, IsInternal
{
    use HasDumpContext;

    private Condition $condition;
    private Converter $converter;

    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('condition', Parameter::TYPE_STRING, true)
            ->addParameter('converter', Converter::class, true)
            ->process($parameters);

        $this->condition = (new ConditionBuilder($this->dumpContext))->build($input->get('condition'));
        $this->converter = $input->get('converter');
    }

    public function convert(mixed $value): mixed
    {
        return $this->condition->evaluate() ? $this->converter->convert($value) : $value;
    }

    public static function getAlternative(): string
    {
        return 'Use the converter option `condition` instead.';
    }
}
