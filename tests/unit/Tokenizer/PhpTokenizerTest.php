<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Tokenizer\Token;

use Smile\GdprDump\Tests\Unit\TestCase;
use Smile\GdprDump\Tokenizer\PhpTokenizer;

class PhpTokenizerTest extends TestCase
{
    /**
     * Test the PHP tokenizer.
     */
    public function testTokenizer()
    {
        $tokenizer = new PhpTokenizer();
        $tokens = $tokenizer->tokenize('<?php "Hello world!" ?>');
        $this->assertNotEmpty($tokens);

        if (!empty($tokens)) {
            $token = current($tokens);
            $this->assertSame(T_OPEN_TAG, $token->getType());
            $this->assertSame('<?php ', $token->getValue());

            $token = end($tokens);
            $this->assertSame(T_CLOSE_TAG, $token->getType());
            $this->assertSame('?>', $token->getValue());
        }
    }
}
