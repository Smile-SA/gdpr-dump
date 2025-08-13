<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Definition;

use RuntimeException;
use Smile\GdprDump\Config\Definition\Table\SortOrder;
use Smile\GdprDump\Config\Definition\Table\WhereExprValidator;
use Smile\GdprDump\Config\Exception\MappingException;

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
            $whereExprValidator = new WhereExprValidator();
            $whereExprValidator->validate($where);
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
        if (!$object instanceof static) {
            $message = 'Cannot merge object of instance "%s" into object of instance "%s".';
            throw new RuntimeException(sprintf($message, get_class($object), get_class($this)));
        }

        foreach ($object->data as $key => $value) {
            $this->data[$key] = $value;
        }

        return $this;
    }

    public function fromArray(array $items): static
    {
        if (array_key_exists('filters', $items)) {
            throw new MappingException('The table property "filters" is no longer supported, use "where" instead.');
        }

        // phpcs:disable Generic.Files.LineLength.TooLong
        foreach ($items as $property => $value) {
            $items[$property] = match ($property) {
                'truncate' => $this->setTruncate($value),
                'where' => $this->setWhere($value),
                'limit' => $this->setLimit($value),
                'order_by' => $this->setSortOrders(is_array($value) ? $value : $this->buildSortOrders($value)),
                'skip_conversion_if' => $this->setSkipCondition($value),
                // Remove disabled converters
                'converters' => $this->setConvertersConfig(
                    array_map(
                        fn (array $converter) => (new ConverterConfig($converter['converter']))->fromArray($converter),
                        array_filter(
                            $value,
                            fn (array $converter) => !array_key_exists('disabled', $converter) || !$converter['disabled']
                        )
                    )
                ),
                'filters' => throw new MappingException('The table property "filters" is no longer supported, use "where" instead.'),
                default => throw new MappingException(sprintf('Unsupported table property "%s".', $property)),
            };
        }
        // phpcs:enable Generic.Files.LineLength.TooLong

        return $this;
    }

    /**
     * Create an array of SortOrder objects from the specified string.
     *
     * @return SortOrder[]
     */
    private function buildSortOrders(string $orderBy): array
    {
        $result = [];
        $orders = explode(',', $orderBy);
        $orders = array_map('trim', $orders);

        foreach ($orders as $order) {
            $parts = explode(' ', $order);

            if (count($parts) > 2) {
                throw new MappingException(sprintf('The sort order "%s" is not valid.', $order));
            }

            $column = $parts[0];
            $direction = $parts[1] ?? SortOrder::DIRECTION_ASC;

            $result[] = new SortOrder($column, $direction);
        }

        return $result;
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
