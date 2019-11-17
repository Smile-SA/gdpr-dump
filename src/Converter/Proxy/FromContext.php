<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use InvalidArgumentException;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Helper\ArrayHelper;

class FromContext implements ConverterInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        if (!array_key_exists('key', $parameters)) {
            throw new InvalidArgumentException('The parameter "key" is required.');
        }

        $this->key = (string) $parameters['key'];
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return ArrayHelper::getPath($context, $this->key);
    }
}
