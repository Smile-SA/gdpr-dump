<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config;

use Smile\GdprDump\Config\Exception\MappingException;
use Smile\GdprDump\Config\Loader\Container;

final class TableConfig extends Container
{
    public function isTruncate(): bool
    {
        return $this->get('truncate', false);
    }

    public function setTruncate(bool $truncate): self
    {
        return $this->set('truncate', $truncate);
    }

    public function getWhere(): string
    {
        return $this->get('where', '');
    }

    public function setWhere(string $where): self
    {
        return $this->set('where', $where);
    }

    public function getLimit(): ?int
    {
        return $this->get('limit');
    }

    public function setLimit(?int $limit): self
    {
        return $this->set('limit', $limit);
    }

    public function getOrderBy(): string
    {
        return $this->get('order_by', '');
    }

    public function setOrderBy(string $orderBy): self
    {
        return $this->set('order_by', $orderBy);
    }

    public function getSkipCondition(): string
    {
        return $this->get('skip_condition_if', '');
    }

    public function setSkipCondition(string $skipCondition): self
    {
        return $this->set('skip_condition_if', $skipCondition);
    }

    /**
     * @return array<string, ConverterConfig>
     */
    public function getConvertersConfig(): array
    {
        return $this->get('converters', []);
    }

    /**
     * @param array<string, ConverterConfig> $convertersConfig
     */
    public function setConvertersConfig(array $convertersConfig): self
    {
        return $this->set('converters', $convertersConfig);
    }

    public function fromArray(array $items): self
    {
        if (array_key_exists('filters', $items)) {
            throw new MappingException('The table property "filters" is no longer supported, use "where" instead.');
        }

        foreach ($items as $property => $value) {
            $items[$property] = match ($property) {
                'truncate' => $this->setTruncate($value),
                'where' => $this->setWhere($value),
                'limit' => $this->setLimit($value),
                'order_by' => $this->setOrderBy($value),
                'skip_conversion_if' => $this->setSkipCondition($value),
                // Remove disabled converters
                'converters' => $this->setConvertersConfig(
                    array_map(
                        fn (array $converter) => (new ConverterConfig($converter['converter']))->fromArray($converter),
                        array_filter(
                            $value,
                            fn (array $converter) => !array_key_exists('disabled', $converter) || !$converter['disabled'],
                        ),
                    )
                ),
                'filters' => throw new MappingException('The table property "filters" is no longer supported, use "where" instead.'),
                default => throw new MappingException(sprintf('Unsupported table property "%s".', $property)),
            };
        }

        return $this;
    }
}
