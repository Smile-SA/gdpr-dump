<?php
declare(strict_types=1);

namespace Smile\GdprDump\Config\Version;

class VersionCondition
{
    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var array
     */
    private static $versionOperators = ['<', '>', '<=', '>=', '<>'];

    /**
     * @param string $condition
     * @throws InvalidVersionException
     */
    public function __construct(string $condition)
    {
        $this->parseCondition($condition);
    }

    /**
     * Get the version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get the operator.
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Get the version and operator from a condition, e.g. `<=2.3`.
     *
     * @param string $condition
     * @throws InvalidVersionException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function parseCondition(string $condition)
    {
        if (strlen($condition) < 3) {
            throw new InvalidVersionException(sprintf('Invalid condition "%s".', $condition));
        }

        $start = substr($condition, 0, 2);

        if (in_array($start, static::$versionOperators, true)) {
            $operator = $start;
            $version = substr($condition, 2, strlen($condition) - 1);
        } elseif (in_array($start[0], static::$versionOperators, true)) {
            $operator = $start[0];
            $version = substr($condition, 1, strlen($condition) - 1);
        } else {
            $operator = '==';
            $version = $condition;
        }

        if (!preg_match('/^[a-zA-Z0-9.-]+$/', $version)) {
            throw new InvalidVersionException(sprintf('Invalid version "%s".', $version));
        }

        $this->operator = $operator;
        $this->version = trim($version);
    }
}
