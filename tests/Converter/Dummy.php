<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Converter;

use Smile\Anonymizer\Converter\ConverterInterface;

class Dummy implements ConverterInterface
{
    /**
     * @var string
     */
    private $prefix;

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
