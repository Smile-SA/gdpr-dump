<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Converters;

use Smile\GdprDump\Converter\Converter;
use Smile\GdprDump\Converter\Exception\InvalidParameterException;
use Smile\GdprDump\Converter\IsConfigurable;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;

final class Hash implements Converter, IsConfigurable
{
    private string $algorithm;

    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('algorithm', Parameter::TYPE_STRING, true, 'sha1')
            ->process($parameters);

        $this->algorithm = $input->get('algorithm');
        $allowed = hash_algos();

        if (!in_array($this->algorithm, $allowed, true)) {
            throw new InvalidParameterException(
                sprintf('Invalid algorithm "%s". Allowed values: %s.', $this->algorithm, implode(', ', $allowed))
            );
        }
    }

    public function convert(mixed $value): string
    {
        $value = (string) $value;

        return $value !== '' ? hash($this->algorithm, $value) : $value;
    }
}
