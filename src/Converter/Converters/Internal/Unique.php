<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Converters\Internal;

use OverflowException;
use Smile\GdprDump\Converter\Converter;
use Smile\GdprDump\Converter\IsConfigurable;
use Smile\GdprDump\Converter\IsInternal;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;

final class Unique implements Converter, IsConfigurable, IsInternal
{
    private Converter $converter;
    private int $maxRetries;
    private array $generated = [];

    public static function getAlternative(): string
    {
        return 'Use the converter option `unique` instead.';
    }

    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('converter', Converter::class, true)
            ->addParameter('max_retries', Parameter::TYPE_INT, true, 100)
            ->process($parameters);

        $this->converter = $input->get('converter');
        $this->maxRetries = $input->get('max_retries');
    }

    public function convert(mixed $value): mixed
    {
        $count = 0;

        do {
            $result = $this->converter->convert($value);

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
