<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Definition;

use Smile\GdprDump\Configuration\Exception\InvalidConfigException;

final class ConverterConfig
{
    private array $parameters = [];
    private string $condition = '';
    private string $cacheKey = '';
    private bool $unique = false;

    /**
     * @throws InvalidConfigException
     */
    public function __construct(private string $name)
    {
        if ($this->name === '') {
            throw new InvalidConfigException('The converter name is required.'); // TODO exception class
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getCondition(): string
    {
        return $this->condition;
    }

    public function setCondition(string $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    public function setCacheKey(string $cacheKey): self
    {
        $this->cacheKey = $cacheKey;

        return $this;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function setUnique(bool $unique): self
    {
        $this->unique = $unique;

        return $this;
    }
}
