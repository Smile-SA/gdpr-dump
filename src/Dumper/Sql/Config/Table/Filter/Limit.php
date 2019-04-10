<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Dumper\Sql\Config\Table\Filter;

class Limit
{
    /**
     * @var int
     */
    private $limit;

    /**
     * @param int $limit
     */
    public function __construct(int $limit)
    {
        $this->limit = $limit;
    }

    /**
     * Get the limit.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }
}
