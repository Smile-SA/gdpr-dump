<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Setter;

use Smile\GdprDump\Converter\ConverterInterface;

class SetValue implements ConverterInterface
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param array $parameters
     * @throws \InvalidArgumentException
     */
    public function __construct(array $parameters)
    {
        if (!array_key_exists('value', $parameters)) {
            throw new \InvalidArgumentException('The setValue converter requires a "value" parameter.');
        }

        $this->value = $parameters['value'];
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return $this->value;
    }
}
