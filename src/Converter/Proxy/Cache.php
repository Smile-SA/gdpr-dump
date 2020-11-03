<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

class Cache implements ConverterInterface
{
    /**
     * @var array
     */
    private static $values;

    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @param array $parameters
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
    public function convert($value, array $context = [])
    {
        if (!isset(static::$values[$this->cacheKey][$value])) {
            static::$values[$this->cacheKey][$value] = $this->converter->convert($value, $context);
        }

        return static::$values[$this->cacheKey][$value];
    }
}
