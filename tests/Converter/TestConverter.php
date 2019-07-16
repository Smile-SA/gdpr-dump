<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Converter;

use Smile\GdprDump\Converter\ConverterInterface;

class TestConverter implements ConverterInterface
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->prefix = $parameters['prefix'] ?? 'test_';
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return $this->prefix . $value;
    }
}
