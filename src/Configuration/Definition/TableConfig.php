<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Definition;

use RuntimeException;
use Smile\GdprDump\Configuration\Definition\Table\SortOrder;
use Smile\GdprDump\Configuration\Validator\WhereExprValidator;

final class TableConfig
{
    private const FIELD_TRUNCATE = 'truncate';
    private const FIELD_WHERE = 'where';
    private const FIELD_LIMIT = 'limit';
    private const FIELD_SKIP_CONDITION = 'skip_condition';
    private const FIELD_ORDERS = 'sort_orders';
    private const FIELD_CONVERTERS = 'converters';

    /**
     * Properties are stored in an array in order to track user-defined values.
     * This allows to merge TableConfig objects in the ConfigProcessor class.
     *
     * @see TableConfig::shallowMerge()
     */
    private array $data = [];

    public function isTruncate(): bool
    {
        return $this->data[self::FIELD_TRUNCATE] ?? false;
    }

    public function setTruncate(bool $truncate): self
    {
        $this->data[self::FIELD_TRUNCATE] = $truncate;

        return $this;
    }

    public function getWhere(): string
    {
        return $this->data[self::FIELD_WHERE] ?? '';
    }

    public function setWhere(string $where): self
    {
        if ($where !== '') {
            (new WhereExprValidator())->validate($where);
        }

        $this->data[self::FIELD_WHERE] = $where;

        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->data[self::FIELD_LIMIT] ?? null;
    }

    public function setLimit(?int $limit): self
    {
        $this->data[self::FIELD_LIMIT] = $limit;

        return $this;
    }

    public function getSkipCondition(): string
    {
        return $this->data[self::FIELD_SKIP_CONDITION] ?? '';
    }

    public function setSkipCondition(string $skipCondition): self
    {
        $this->data[self::FIELD_SKIP_CONDITION] = $skipCondition;

        return $this;
    }

    /**
     * @return array<string, SortOrder>
    */
    public function getSortOrders(): array
    {
        return $this->data[self::FIELD_ORDERS] ?? [];
    }

    /**
     * @param array<string, SortOrder> $sortOrders
     */
    public function setSortOrders(array $sortOrders): self
    {
        $this->data[self::FIELD_ORDERS] = $sortOrders;

        return $this;
    }

    /**
     * @return array<string, ConverterConfig>
     */
    public function getConvertersConfig(): array
    {
        return $this->data[self::FIELD_CONVERTERS] ?? [];
    }

    /**
     * @param array<string, ConverterConfig> $convertersConfig
     */
    public function setConvertersConfig(array $convertersConfig): self
    {
        $this->data[self::FIELD_CONVERTERS] = $convertersConfig;

        return $this;
    }

    /**
     * Perform a shallow merge on the object (user-defined data is copied, not merged recurively).
     *
     * @throws RuntimeException
     */
    public function shallowMerge(self $object): static
    {
        foreach ($object->data as $key => $value) {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Deep clone the object.
     */
    public function __clone(): void
    {
        if (array_key_exists(self::FIELD_ORDERS, $this->data)) {
            $this->setSortOrders(array_map(fn (SortOrder $item) => clone $item, $this->getSortOrders()));
        }

        if (array_key_exists(self::FIELD_CONVERTERS, $this->data)) {
            $this->setConvertersConfig(
                array_map(fn (ConverterConfig $item) => clone $item, $this->getConvertersConfig())
            );
        }
    }
}
