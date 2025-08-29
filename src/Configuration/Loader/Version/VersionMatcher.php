<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Loader\Version;

use Smile\GdprDump\Configuration\Exception\ParseException;

final class VersionMatcher
{
    /**
     * Check whether the requirement (e.g. ">=1.0.0 <2.0.0") matches the specified version (e.g. "1.1.0").
     *
     * @throws ParseException
     */
    public function match(string $requirement, string $version): bool
    {
        $match = true;

        // Check if all the conditions match the version
        foreach ($this->getConditions($requirement) as $condition) {
            if (!version_compare($version, $condition->getVersion(), $condition->getOperator())) {
                $match = false;
                break;
            }
        }

        return $match;
    }

    /**
     * Get the conditions that are part of the requirement.
     *
     * @return VersionCondition[]
     * @throws ParseException
     */
    private function getConditions(string $requirement): array
    {
        $conditions = [];
        $parts = explode(' ', $requirement);

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $conditions[] = new VersionCondition($part);
        }

        return $conditions;
    }
}
