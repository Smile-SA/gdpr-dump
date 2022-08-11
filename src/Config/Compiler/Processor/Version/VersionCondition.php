<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Compiler\Processor\Version;

class VersionCondition
{
    /**
     * @var string[]
     */
    private static array $versionOperators = ['<', '>', '<=', '>=', '<>'];

    private string $version;
    private string $operator;

    /**
     * @throws InvalidVersionException
     */
    public function __construct(string $condition)
    {
        $this->parseCondition($condition);
    }

    /**
     * Get the version.
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get the operator.
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * Get the version and operator from a condition, e.g. `<=2.3`.
     *
     * @throws InvalidVersionException
     */
    private function parseCondition(string $condition): void
    {
        if (strlen($condition) < 3) {
            throw new InvalidVersionException(sprintf('Invalid condition "%s".', $condition));
        }

        $start = substr($condition, 0, 2);

        if (in_array($start, self::$versionOperators, true)) {
            $operator = $start;
            $version = substr($condition, 2, strlen($condition) - 1);
        } elseif (in_array($start[0], self::$versionOperators, true)) {
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
