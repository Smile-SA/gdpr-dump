<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use InvalidArgumentException;
use Smile\GdprDump\Converter\ConditionBuilder;
use Smile\GdprDump\Converter\ConverterInterface;
use UnexpectedValueException;

class Conditional implements ConverterInterface
{
    /**
     * @var string
     */
    private $condition;

    /**
     * @var ConverterInterface|null
     */
    private $ifTrueConverter;

    /**
     * @var ConverterInterface|null
     */
    private $ifFalseConverter;

    /**
     * @param array $parameters
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function __construct(array $parameters)
    {
        if (!array_key_exists('condition', $parameters)) {
            throw new InvalidArgumentException('The parameter "condition" is required.');
        }

        $condition = (string) $parameters['condition'];
        if ($condition === '') {
            throw new UnexpectedValueException('The parameter "condition" must not be empty.');
        }

        if (!isset($parameters['if_true_converter']) && !isset($parameters['if_false_converter'])) {
            throw new InvalidArgumentException(
                'The conditional converter requires a "if_true_converter" and/or "if_false_converter" parameter.'
            );
        }

        $conditionBuilder = new ConditionBuilder();
        $this->condition = $conditionBuilder->build($condition);

        if (isset($parameters['if_true_converter'])) {
            $this->ifTrueConverter = $parameters['if_true_converter'];
        }

        if (isset($parameters['if_false_converter'])) {
            $this->ifFalseConverter = $parameters['if_false_converter'];
        }
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.EvalExpression)
     */
    public function convert($value, array $context = [])
    {
        $result = (bool) eval($this->condition);

        if ($result) {
            if ($this->ifTrueConverter !== null) {
                $value = $this->ifTrueConverter->convert($value, $context);
            }
        } elseif ($this->ifFalseConverter !== null) {
            $value = $this->ifFalseConverter->convert($value, $context);
        }

        return $value;
    }
}
