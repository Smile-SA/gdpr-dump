<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Transformer;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\ValidationException;

/**
 * @deprecated Use "prependText" instead.
 */
class AddPrefix implements ConverterInterface
{
    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @param array $parameters
     * @throws ValidationException
     */
    public function __construct(array $parameters = [])
    {
        if (array_key_exists('prefix', $parameters)) {
            $parameters['value'] = $parameters['prefix'];
            unset($parameters['prefix']);
        }

        $this->converter = new PrependText($parameters);
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return $this->converter->convert($value, $context);
    }
}
