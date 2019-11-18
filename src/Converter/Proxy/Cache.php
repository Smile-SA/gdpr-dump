<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use InvalidArgumentException;
use Smile\GdprDump\Converter\ConverterInterface;

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
     */
    public function __construct(array $parameters)
    {
        if (!isset($parameters['converter'])) {
            throw new InvalidArgumentException('The parameter "converter" is required.');
        }

        if (!isset($parameters['cache_key'])) {
            throw new InvalidArgumentException('The parameter "cache_key" is required.');
        }

        $this->converter = $parameters['converter'];
        $this->cacheKey = (string) $parameters['cache_key'];
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
