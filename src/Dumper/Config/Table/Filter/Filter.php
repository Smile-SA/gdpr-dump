<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config\Table\Filter;

use UnexpectedValueException;

class Filter
{
    const OPERATOR_EQ = 'eq';
    const OPERATOR_NEQ = 'neq';
    const OPERATOR_LT = 'lt';
    const OPERATOR_LTE = 'lte';
    const OPERATOR_GT = 'gt';
    const OPERATOR_GTE = 'gte';
    const OPERATOR_IS_NULL = 'isNull';
    const OPERATOR_IS_NOT_NULL = 'isNotNull';
    const OPERATOR_LIKE = 'like';
    const OPERATOR_NOT_LIKE = 'notLike';
    const OPERATOR_IN = 'in';
    const OPERATOR_NOT_IN = 'notIn';

    /**
     * @var string[]
     */
    private static $operators = [
        self::OPERATOR_EQ,
        self::OPERATOR_NEQ,
        self::OPERATOR_LT,
        self::OPERATOR_LTE,
        self::OPERATOR_GT,
        self::OPERATOR_GTE,
        self::OPERATOR_IS_NULL,
        self::OPERATOR_IS_NOT_NULL,
        self::OPERATOR_LIKE,
        self::OPERATOR_NOT_LIKE,
        self::OPERATOR_IN,
        self::OPERATOR_NOT_IN,
    ];

    /**
     * @var string
     */
    private $column;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string $column
     * @param string $operator
     * @param mixed $value
     */
    public function __construct(string $column, string $operator, $value = null)
    {
        if (!in_array($operator, self::$operators)) {
            throw new UnexpectedValueException(sprintf('Invalid filter operator "%s".', $operator));
        }

        if (is_array($value) && !in_array($operator, [self::OPERATOR_IN, self::OPERATOR_NOT_IN])) {
            throw new UnexpectedValueException(
                sprintf('The "%s" operator is not compatible with array values.', $operator)
            );
        }

        $this->column = $column;
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * Get the column name.
     *
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * Get the filter operator
     *
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * Get the filter value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
