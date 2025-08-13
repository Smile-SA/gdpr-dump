<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config\Definition;

use UnexpectedValueException;

final class ConverterConfig
{
    private string $name;
    private string $condition = '';
    private string $cacheKey = '';
    private bool $unique = false;

    /**
     * @var array<string, mixed>
     */
    private array $parameters = [];

    public function __construct(array $converterData)
    {
        $name = (string) ($converterData['converter'] ?? '');
        if ($name === '') {
            throw new UnexpectedValueException('The converter name is required.');
        }

        $this->name = (string) $converterData['converter'];
        $this->condition = (string) ($converterData['condition'] ?? '');
        $this->cacheKey = (string) ($converterData['cache_key'] ?? '');
        $this->unique = (bool) ($converterData['unique'] ?? false);
        $this->parameters = (array) ($converterData['parameters'] ?? []);
    }

    /**
     * Get the converter name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the condition to evaluate.
     */
    public function getCondition(): string
    {
        return $this->condition;
    }

    /**
     * Get the condition.
     */
    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    /**
     * Check if unique values must be generated.
     */
    public function isUnique(): bool
    {
        return $this->unique;
    }

    /**
     * Get the parameters.
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
