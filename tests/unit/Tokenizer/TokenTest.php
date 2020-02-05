<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Tokenizer\Token;

use Smile\GdprDump\Tests\Unit\TestCase;
use Smile\GdprDump\Tokenizer\Token;

class TokenTest extends TestCase
{
    /**
     * Test the token creation and the getter methods.
     */
    public function testGetters()
    {
        $type = T_STRING;
        $value = 'value';
        $token = new Token($type, $value);

        $this->assertSame($type, $token->getType());
        $this->assertSame($value, $token->getValue());
    }
}
