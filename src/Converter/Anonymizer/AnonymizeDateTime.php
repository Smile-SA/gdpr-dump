<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Anonymizer;

use Smile\GdprDump\Converter\ConverterInterface;

final class AnonymizeDateTime implements ConverterInterface
{
    private AnonymizeDate $dateConverter;

    /**
     * @inheritdoc
     */
    public function setParameters(array $parameters): void
    {
        if (!array_key_exists('format', $parameters)) {
            $parameters['format'] = 'Y-m-d H:i:s';
        }

        $this->dateConverter = new AnonymizeDate();
        $this->dateConverter->setParameters($parameters);
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): string
    {
        return $this->dateConverter->convert($value, $context);
    }
}
