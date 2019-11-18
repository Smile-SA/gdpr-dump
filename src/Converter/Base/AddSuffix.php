<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Base;

use InvalidArgumentException;
use Smile\GdprDump\Converter\ConverterInterface;

class AddSuffix implements ConverterInterface
{
    /**
     * @var string
     */
    private $suffix;

    /**
     * @param array $parameters
     * @throws InvalidArgumentException
     */
    public function __construct(array $parameters = [])
    {
        if (!array_key_exists('suffix', $parameters)) {
            throw new InvalidArgumentException('The parameter "suffix" is required.');
        }

        $this->suffix = (string) $parameters['suffix'];
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return $value . $this->suffix;
    }
}
