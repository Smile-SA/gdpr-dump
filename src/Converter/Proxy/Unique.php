<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use OverflowException;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;

class Unique implements ConverterInterface
{
    private ConverterInterface $converter;
    private int $maxRetries;
    private array $generated = [];

    /**
     * @inheritdoc
     */
    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('converter', ConverterInterface::class, true)
            ->addParameter('max_retries', Parameter::TYPE_INT, true, 100)
            ->process($parameters);

        $this->converter = $input->get('converter');
        $this->maxRetries = $input->get('max_retries');
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): mixed
    {
        $count = 0;

        do {
            $result = $this->converter->convert($value, $context);

            // Ignore null values
            if ($result === null) {
                return null;
            }

            $count++;
            if ($count > $this->maxRetries) {
                throw new OverflowException(
                    sprintf('Maximum retries of %d reached without finding a unique value.', $this->maxRetries)
                );
            }

            $key = serialize($result);
        } while (array_key_exists($key, $this->generated));

        $this->generated[$key] = null;

        return $result;
    }
}
