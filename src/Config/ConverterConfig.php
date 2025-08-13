<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config;

use Smile\GdprDump\Config\Exception\MappingException;
use Smile\GdprDump\Config\Loader\Container;

final class ConverterConfig extends Container
{
    public function __construct(string $name)
    {
        $this->setName($name);
    }

    public function getName(): string
    {
        return $this->get('name', '');
    }

    public function setName(string $name): self
    {
        return $this->set('name', $name);
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->get('parameters', []);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function setParameters(array $parameters): self
    {
        return $this->set('parameters', $parameters);
    }

    public function getCondition(): string
    {
        return $this->get('condition', '');
    }

    public function setCondition(string $condition): self
    {
        return $this->set('condition', $condition);
    }

    public function getCacheKey(): string
    {
        return $this->get('cache_key', '');
    }

    public function setCacheKey(string $cacheKey): self
    {
        return $this->set('cache_key', $cacheKey);
    }

    public function isUnique(): bool
    {
        return $this->get('unique', false);
    }

    public function setUnique(bool $unique): self
    {
        return $this->set('unique', $unique);
    }

    public function fromArray(array $items): self
    {
        foreach ($items as $property => $value) {
            $items[$property] = match ($property) {
                'parameters' => $this->setParameters($value),
                'condition' => $this->setCondition($value),
                'cache_key' => $this->setCacheKey($value),
                'unique' => $this->setUnique($value),
                'converter' => $this->setName($value),
                default => throw new MappingException(sprintf('Unsupported converter property "%s".', $property)),
            };
        }

        return $this;
    }
}
