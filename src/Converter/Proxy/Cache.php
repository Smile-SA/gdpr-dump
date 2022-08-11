<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

class Cache implements ConverterInterface
{
    private static array $values = [];
    private ConverterInterface $converter;
    private string $cacheKey;

    /**
     * @throws ValidationException
     */
    public function __construct(array $parameters)
    {
        $input = (new ParameterProcessor())
            ->addParameter('converter', ConverterInterface::class, true)
            ->addParameter('cache_key', Parameter::TYPE_STRING, true)
            ->process($parameters);

        $this->converter = $input->get('converter');
        $this->cacheKey = $input->get('cache_key');
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): mixed
    {
        if (!isset(self::$values[$this->cacheKey][$value])) {
            self::$values[$this->cacheKey][$value] = $this->converter->convert($value, $context);
        }

        return self::$values[$this->cacheKey][$value];
    }
}
