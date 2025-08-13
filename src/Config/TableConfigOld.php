<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config;

use Smile\GdprDump\Dumper\Config\Definition\Table\SortOrder;
use Smile\GdprDump\Dumper\Config\Validation\WhereExprValidator;

final class TableConfigOld
{
    private bool $truncate = false;
    private string $where = '';
    private ?int $limit = null;
    private string $skipCondition = '';

    /**
     * @var SortOrder[]
     */
    private array $sortOrders = [];

    /**
     * @var array<string, ConverterConfig>
     */
    private array $convertersConfig = [];

    private array $data = [
        'truncate' => false,
        'where' => '',
        'limit' => null,
        'skipCondition' => '',
        'sortOrders' => [],
        'convertersConfig' => [],
    ];

    private array $changed = [];

    public function isTruncate(): bool
    {
        return $this->data['truncate'];
        return $this->truncate;
    }

    public function setTruncate(bool $truncate): self
    {
        $this->data['truncate'] = $truncate;
        $this->data['changed'] = 'truncate';

        return $this;
        $this->truncate = $truncate;

        return $this;
    }

    public function getWhere(): string
    {
        return $this->where;
    }

    public function setWhere(string $where): self
    {
        if ($where !== '') {
            $whereExprValidator = new WhereExprValidator();
            $whereExprValidator->validate($where);
        }

        $this->where = $where;
        $this->changed[] = 'where';

        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): self
    {
        $this->limit = $limit;
        $this->changed[] = 'limit';

        return $this;
    }

    public function getSortOrders(): array
    {
        return $this->sortOrders;
    }

    public function setSortOrders(array $sortOrders): self
    {
        $this->sortOrders = $sortOrders;
        $this->changed[] = 'sortOrders';

        return $this;
    }

    public function getSkipCondition(): string
    {
        return $this->skipCondition;
    }

    public function setSkipCondition(string $skipCondition): self
    {
        $this->skipCondition = $skipCondition;
        $this->changed[] = 'skipCondition';

        return $this;
    }

    /**
     * @return array<string, ConverterConfig>
     */
    public function getConvertersConfig(): array
    {
        return $this->convertersConfig;
    }

    /**
     * @param array<string, ConverterConfig> $convertersConfig
     */
    public function setConvertersConfig(array $convertersConfig): self
    {
        $this->convertersConfig = $convertersConfig;
        $this->changed[] = 'convertersConfig';

        return $this;
    }

    public function getChangedFields()
    {

    }
}
