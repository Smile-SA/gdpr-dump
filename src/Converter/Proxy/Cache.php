<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use InvalidArgumentException;
use Smile\GdprDump\Converter\ConverterInterface;
use UnexpectedValueException;

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
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function __construct(array $parameters)
    {
        if (!isset($parameters['converter'])) {
            throw new InvalidArgumentException('The parameter "converter" is required.');
        }

        if (!array_key_exists('cache_key', $parameters)) {
            throw new InvalidArgumentException('The parameter "cache_key" is required.');
        }

        $this->converter = $parameters['converter'];
        $this->cacheKey = (string) $parameters['cache_key'];

        if ($this->cacheKey === '') {
            throw new UnexpectedValueException('The parameter "cache_key" must not be empty.');
        }
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
