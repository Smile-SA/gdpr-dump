<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Definition;

use Smile\GdprDump\Config\Exception\MappingException;
use UnexpectedValueException;

final class ConverterConfig
{
    private array $parameters = [];
    private string $condition = '';
    private string $cacheKey = '';
    private bool $unique = false;

    /**
     * @throws UnexpectedValueException
     */
    public function __construct(private string $name)
    {
        if ($this->name === '') {
            throw new UnexpectedValueException('The converter name is required.');
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

    public function fromArray(array $items): static
    {
        foreach ($items as $property => $value) {
            $items[$property] = match ($property) {
                'converter' => $this->setName($value),
                'parameters' => $this->setParameters($value),
                'where' => $this->setCondition($value),
                'cache_key' => $this->setCacheKey($value),
                'unique' => $this->setUnique($value),
                default => throw new MappingException(sprintf('Unsupported converter property "%s".', $property)),
            };
        }

        return $this;
    }
}
