<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Compiler\Processor;

use Smile\GdprDump\Configuration\Compiler\CompilerStep;
use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Configuration\Loader\Container;
use Smile\GdprDump\Util\Objects;
use stdClass;

class ConverterTemplatesProcessor implements Processor
{
    public function getStep(): CompilerStep
    {
        return CompilerStep::AFTER_VALIDATION;
    }

    /**
     * Process the "converter_templates" parameter.
     */
    public function process(Container $container): void
    {
        $templates = $container->get('converter_templates');
        if (!$templates) {
            return;
        }

        foreach (get_object_vars($container->get('tables')) as $tableConfig) {
            if (!property_exists($tableConfig, 'converters')) {
                continue;
            }

            foreach (get_object_vars($tableConfig->converters) as $column => $converterConfig) {
                $tableConfig->converters->{$column} = $this->applyTemplateToConverter($converterConfig, $templates);
            }
        }
    }

    /**
     * Try to apply a converter template to the specified converter object.
     */
    private function applyTemplateToConverter(stdClass $converterConfig, stdClass $templates): stdClass
    {
        if (!property_exists($converterConfig, 'converter')) {
            return $converterConfig; // not supposed to happen but better safe than sorry
        }

        // Apply template to parameters (e.g. the "chain" converter has a list of converters as one of its parameters)
        $this->applyTemplateToParameters($converterConfig, $templates);

        // Apply template to the converter itself
        $candidateTemplate = $converterConfig->converter;
        if (!property_exists($templates, $candidateTemplate)) {
            return $converterConfig;
        }

        $templateCopy = Objects::deepClone($templates->{$candidateTemplate});
        $converterName = $templateCopy->converter;
        if (property_exists($templates, $converterName)) {
            throw new ParseException('Nested converter templates are not supported.');
        }

        Objects::merge($templateCopy, $converterConfig);
        $templateCopy->converter = $converterName;

        return $templateCopy;
    }

    /**
     * Try to apply a converter template to the parameters of the specified converter object.
     */
    private function applyTemplateToParameters(stdClass $converterConfig, stdClass $templates): void
    {
        if (!property_exists($converterConfig, 'parameters')) {
            return;
        }

        $parameters = $converterConfig->parameters;

        // "converters" parameter (type array)
        if (property_exists($parameters, 'converters') && is_array($parameters->converters)) {
            $parameters->converters = array_map(
                fn (stdClass $item) => $this->applyTemplateToConverter($item, $templates),
                $parameters->converters
            );
            return;
        }

        // "converters" parameter (type object)
        if (property_exists($parameters, 'converters') && $parameters->converters instanceof stdClass) {
            foreach (get_object_vars($parameters->converters) as $key => $value) {
                $parameters->converters->{$key} = $this->applyTemplateToConverter($value, $templates);
            }
            return;
        }

        // "converter" parameter (type object)
        if (property_exists($parameters, 'converter') && $parameters->converter instanceof stdClass) {
            $parameters->converter = $this->applyTemplateToConverter(
                $parameters->converter,
                $templates
            );
        }
    }
}
