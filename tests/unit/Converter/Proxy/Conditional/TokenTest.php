<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy\Conditional;

use Smile\GdprDump\Converter\Proxy\Conditional\Token;
use Smile\GdprDump\Tests\Unit\TestCase;

class TokenTest extends TestCase
{
    /**
     * Test the token creation with array data.
     */
    public function testArrayData()
    {
        $tokenData = [T_CONSTANT_ENCAPSED_STRING, 'value'];
        $token = new Token($tokenData);

        $this->assertSame($tokenData[0], $token->getType());
        $this->assertSame($tokenData[1], $token->getValue());
    }

    /**
     * Test the token creation with string data.
     */
    public function testStringData()
    {
        $tokenData = 'value';
        $token = new Token($tokenData);

        $this->assertSame(Token::T_UNKNOWN, $token->getType());
        $this->assertSame($tokenData, $token->getValue());
    }
}
