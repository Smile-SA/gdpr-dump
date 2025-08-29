<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Converters;

use Smile\GdprDump\Converter\Converter;
use Smile\GdprDump\Converter\IsConfigurable;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Util\Arrays;

final class JsonData implements Converter, IsConfigurable
{
    /**
     * @var array<string, Converter>
     */
    private array $converters;

    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('converters', Parameter::TYPE_ARRAY, true)
            ->process($parameters);

        $this->converters = $input->get('converters');
    }

    public function convert(mixed $value): mixed
    {
        $decoded = json_decode((string) $value, true);
        if (!is_array($decoded)) {
            return $value;
        }

        foreach ($this->converters as $path => $converter) {
            // Get the value
            $nestedValue = Arrays::getPath($decoded, $path);
            if ($nestedValue === null) {
                continue;
            }

            // Format the value
            $nestedValue = $converter->convert($nestedValue);

            // Replace the original value in the JSON by the converted value
            Arrays::setPath($decoded, $path, $nestedValue);
        }

        return json_encode($decoded);
    }
}
