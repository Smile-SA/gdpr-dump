<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy\Conditional;

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
     * Build a token object from the token data.
     *
     * @param array|string $rawToken
     */
    public function __construct($rawToken)
    {
        if (!is_array($rawToken)) {
            $this->value = (string) $rawToken;
            $this->type = self::T_UNKNOWN;
            return;
        }

        $this->type = (int) $rawToken[0];
        $this->value = (string) $rawToken[1];
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
