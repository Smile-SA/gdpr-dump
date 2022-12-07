<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config\Table\Filter;

use UnexpectedValueException;

class Filter
{
    public const OPERATOR_EQ = 'eq';
    public const OPERATOR_NEQ = 'neq';
    public const OPERATOR_LT = 'lt';
    public const OPERATOR_LTE = 'lte';
    public const OPERATOR_GT = 'gt';
    public const OPERATOR_GTE = 'gte';
    public const OPERATOR_IS_NULL = 'isNull';
    public const OPERATOR_IS_NOT_NULL = 'isNotNull';
    public const OPERATOR_LIKE = 'like';
    public const OPERATOR_NOT_LIKE = 'notLike';
    public const OPERATOR_IN = 'in';
    public const OPERATOR_NOT_IN = 'notIn';

    /**
     * @var string[]
     */
    private static array $operators = [
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
     * @throws UnexpectedValueException
     */
    public function __construct(private string $column, private string $operator, private mixed $value = null)
    {
        if (!in_array($operator, self::$operators, true)) {
            throw new UnexpectedValueException(sprintf('Invalid filter operator "%s".', $operator));
        }

        if (is_array($value) && !in_array($operator, [self::OPERATOR_IN, self::OPERATOR_NOT_IN], true)) {
            throw new UnexpectedValueException(
                sprintf('The "%s" operator is not compatible with array values.', $operator)
            );
        }
    }

    /**
     * Get the column name.
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * Get the filter operator.
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * Get the filter value.
     */
    public function getValue(): mixed
    {
        return $this->value;
    }
}
