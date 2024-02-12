<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Transformer;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

class Hash implements ConverterInterface
{
    private string $algorithm;

    /**
     * @inheritdoc
     */
    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('algorithm', Parameter::TYPE_STRING, true, 'sha1')
            ->process($parameters);

        $this->algorithm = $input->get('algorithm');
        $allowed = hash_algos();

        if (!in_array($this->algorithm, $allowed, true)) {
            throw new ValidationException(
                sprintf('Invalid algorithm "%s". Allowed values: %s.', $this->algorithm, implode(', ', $allowed))
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): string
    {
        $value = (string) $value;

        return $value !== '' ? hash($this->algorithm, $value) : $value;
    }
}
