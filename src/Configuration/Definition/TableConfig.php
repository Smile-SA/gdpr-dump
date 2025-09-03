<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Definition;

use Smile\GdprDump\Configuration\Definition\Table\SortOrder;
use Smile\GdprDump\Configuration\Validator\WhereExprValidator;

final class TableConfig
{
    /*
     * Variables don't have a default value, because we need to track which ones are used-defined.
     * It helps determining how to merge TableConfig objects when resolving table names (e.g. `log_*`).
     *
     * @see \Smile\GdprDump\Dumper\Config\TableNameResolver::resolveTableConfigs()
     */
    private string $where;
    private ?int $limit;
    private string $skipCondition;
    private ConverterConfigMap $converterConfigs;

    /**
     * @var SortOrder[]
     */
    private array $sortOrders;

    public function getWhere(): string
    {
        return $this->where ?? '';
    }

    public function setWhere(string $where): self
    {
        if ($where !== '') {
            (new WhereExprValidator())->validate($where);
        }

        $this->where = $where;

        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit ?? null;
    }

    public function setLimit(?int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function getSkipCondition(): string
    {
        return $this->skipCondition ?? '';
    }

    public function setSkipCondition(string $skipCondition): self
    {
        $this->skipCondition = $skipCondition;

        return $this;
    }

    /**
     * @return SortOrder[]
    */
    public function getSortOrders(): array
    {
        return $this->sortOrders ?? [];
    }

    /**
     * @param SortOrder[] $sortOrders
     */
    public function setSortOrders(array $sortOrders): self
    {
        $this->sortOrders = $sortOrders;

        return $this;
    }

    public function getConverterConfigs(): ConverterConfigMap
    {
        if (!isset($this->converterConfigs)) {
            $this->converterConfigs = new ConverterConfigMap();
        }

        return $this->converterConfigs;
    }

    public function setConverterConfigs(ConverterConfigMap $converterConfigs): self
    {
        $this->converterConfigs = $converterConfigs;

        return $this;
    }

    /**
     * Perform a shallow merge (not recursive) with another TableConfig object.
     */
    public function shallowMerge(self $object): static
    {
        // Copy user-defined (= initialized) non-object properties
        foreach (get_object_vars($object) as $property => $value) {
            if (!is_object($value)) {
                $this->{$property} = $value;
                continue;
            }
        }

        // Copy converters
        if (!$object->getConverterConfigs()->isEmpty()) {
            foreach ($object->getConverterConfigs() as $index => $converterConfig) {
                $this->getConverterConfigs()->set($index, $converterConfig);
            }
        }

        return $this;
    }

    /**
     * Deep clone the object.
     */
    public function __clone(): void
    {
        if (isset($this->sortOrders)) {
            $this->setSortOrders(
                array_map(fn (SortOrder $item) => clone $item, $this->getSortOrders())
            );
        }

        if (isset($this->converterConfigs)) {
            $this->setConverterConfigs(clone $this->getConverterConfigs());
        }
    }
}
