<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Converter\Proxy;

use Smile\Anonymizer\Converter\ConverterInterface;

class Chain implements ConverterInterface
{
    /**
     * @var array
     */
    private $converters;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        if (empty($parameters['converters'])) {
            throw new \InvalidArgumentException('The chain converter requires a "converters" parameter.');
        }

        $this->converters = $parameters['converters'];
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        /** @var ConverterInterface $converter */
        foreach ($this->converters as $converter) {
            $value = $converter->convert($value, $context);
        }

        return $value;
    }
}
