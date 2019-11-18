<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use InvalidArgumentException;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Helper\ArrayHelper;
use UnexpectedValueException;

class FromContext implements ConverterInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * @param array $parameters
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function __construct(array $parameters = [])
    {
        if (!array_key_exists('key', $parameters)) {
            throw new InvalidArgumentException('The parameter "key" is required.');
        }

        $this->key = (string) $parameters['key'];

        if ($this->key === '') {
            throw new UnexpectedValueException('The parameter "key" must not be empty.');
        }
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return ArrayHelper::getPath($context, $this->key);
    }
}
