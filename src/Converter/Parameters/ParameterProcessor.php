<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Parameters;

class ParameterProcessor
{
    /**
     * @var Parameter[]
     */
    private array $parameters = [];

    /**
     * Add a parameter.
     */
    public function addParameter(string $name, string $type, bool $required = false, mixed $default = null): self
    {
        $this->parameters[] = new Parameter($name, $type, $required, $default);

        return $this;
    }

    /**
     * Process an array of parameter values.
     * This method handles data validation and type casting.
     *
     * @throws ValidationException
     */
    public function process(array $values): InputParameters
    {
        $processed = [];

        foreach ($this->parameters as $parameter) {
            // Set the default value
            $name = $parameter->getName();
            $value = array_key_exists($name, $values) ? $values[$name] : $parameter->getDefault();

            // Validate the value and cast it to its expected type
            $processed[$name] = $this->processValue($parameter, $value);
        }

        return new InputParameters($processed);
    }

    /**
     * Process a parameter value.
     *
     * @throws ValidationException
     */
    private function processValue(Parameter $parameter, mixed $value): mixed
    {
        if ($parameter->isRequired()) {
            if ($value === null) {
                throw new ValidationException(sprintf('The parameter "%s" is required.', $parameter->getName()));
            }

            if ($value === '' || $value === []) {
                throw new ValidationException(sprintf('The parameter "%s" must not be empty.', $parameter->getName()));
            }
        }

        if ($value !== null) {
            $this->validateType($parameter, $value);

            if ($parameter->isScalar()) {
                settype($value, $parameter->getType());
            }
        }

        return $value;
    }

    /**
     * Assert that the parameter type is allowed.
     *
     * @throws ValidationException
     */
    private function validateType(Parameter $parameter, mixed $value): void
    {
        $name = $parameter->getName();
        $type = $parameter->getType();

        if ($parameter->isArray()) {
            if (!is_array($value)) {
                throw new ValidationException(sprintf('The parameter "%s" must be an array.', $name));
            }
        } elseif ($parameter->isScalar()) {
            if (!is_scalar($value)) {
                throw new ValidationException(sprintf('The parameter "%s" must be a %s.', $name, $type));
            }
        } elseif (!is_object($value) || !$value instanceof $type) {
            throw new ValidationException(sprintf('The parameter "%s" must be an instance of %s.', $name, $type));
        }
    }
}
