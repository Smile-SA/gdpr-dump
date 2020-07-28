<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Transformer;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\ValidationException;

/**
 * @deprecated Use "appendText" instead.
 */
class AddSuffix implements ConverterInterface
{
    /**
     * @var ConverterInterface
     */
    private ConverterInterface $converter;

    /**
     * @param array $parameters
     * @throws ValidationException
     */
    public function __construct(array $parameters = [])
    {
        if (array_key_exists('suffix', $parameters)) {
            $parameters['value'] = $parameters['suffix'];
            unset($parameters['suffix']);
        }

        $this->converter = new AppendText($parameters);
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return $this->converter->convert($value, $context);
    }
}
