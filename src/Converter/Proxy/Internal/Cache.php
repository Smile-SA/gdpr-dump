<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy\Internal;

use Smile\GdprDump\Converter\Converter;
use Smile\GdprDump\Converter\IsInternal;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;

final class Cache implements IsInternal
{
    private static array $values = [];
    private Converter $converter;
    private string $cacheKey;

    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('converter', Converter::class, true)
            ->addParameter('cache_key', Parameter::TYPE_STRING, true)
            ->process($parameters);

        $this->converter = $input->get('converter');
        $this->cacheKey = $input->get('cache_key');
    }

    public function convert(mixed $value): mixed
    {
        if (!isset(self::$values[$this->cacheKey][$value])) {
            self::$values[$this->cacheKey][$value] = $this->converter->convert($value);
        }

        return self::$values[$this->cacheKey][$value];
    }
}
