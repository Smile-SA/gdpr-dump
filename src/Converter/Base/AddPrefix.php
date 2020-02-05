<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Base;

use InvalidArgumentException;
use Smile\GdprDump\Converter\ConverterInterface;

class AddPrefix implements ConverterInterface
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @param array $parameters
     * @throws InvalidArgumentException
     */
    public function __construct(array $parameters = [])
    {
        if (!array_key_exists('prefix', $parameters)) {
            throw new InvalidArgumentException('The parameter "prefix" is required.');
        }

        $this->prefix = (string) $parameters['prefix'];
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return $this->prefix . $value;
    }
}
