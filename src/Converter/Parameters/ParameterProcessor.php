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
     *
     * @param string $name
     * @param string|null $type
     * @param bool $required
     * @param mixed $default
     * @return $this
     */
    public function addParameter(string $name, string $type = null, bool $required = false, $default = null): self
    {
        $this->parameters[] = new Parameter($name, $type, $required, $default);

        return $this;
    }

    /**
     * Process an array of parameter values.
     * This method handles data validation and type casting.
     *
     * @param array $values
     * @return InputParameters
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
     * @param Parameter $parameter
     * @param mixed $value
     * @return mixed
     * @throws ValidationException
     */
    private function processValue(Parameter $parameter, $value)
    {
        if ($parameter->isRequired()) {
            if ($value === null) {
                throw new ValidationException(sprintf('The parameter "%s" is required.', $parameter->getName()));
            }

            if ($value === '' || $value === []) {
                throw new ValidationException(sprintf('The parameter "%s" must not be empty.', $parameter->getName()));
            }
        }

        $type = $parameter->getType();
        if ($type !== null && $value !== null) {
            $this->validateType($parameter, $value);

            if ($parameter->isScalar()) {
                settype($value, $type);
            }
        }

        return $value;
    }

    /**
     * Assert that the parameter type is allowed.
     *
     * @param Parameter $parameter
     * @param mixed $value
     * @throws ValidationException
     */
    private function validateType(Parameter $parameter, $value): void
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
