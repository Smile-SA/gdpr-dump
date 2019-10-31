<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tokenizer;

class Token
{
    /**
     * Unknown token type.
     */
    const T_UNKNOWN = 0;

    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $value;

    /**
     * @param int $type
     * @param string $value
     */
    public function __construct(int $type, string $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Get the token type.
     *
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Get the token value.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
