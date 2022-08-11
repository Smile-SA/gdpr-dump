<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Phar\Compiler\Minify;

use Smile\GdprDump\Phar\Minify\Php;
use Smile\GdprDump\Tests\Unit\TestCase;

class PhpTest extends TestCase
{
    /**
     * Test the "minify" method.
     */
    public function testMinify(): void
    {
        $minifier = new Php();
        $this->assertSame($this->getExpectedMinifiedString(), $minifier->minify($this->getStringToMinify()));
    }

    /**
     * Test the "supports" method.
     */
    public function testSupports(): void
    {
        $minifier = new Php();
        $this->assertTrue($minifier->supports('php'));
        $this->assertFalse($minifier->supports('json'));
        $this->assertFalse($minifier->supports(''));
    }

    /**
     * Get the string to minify.
     */
    private function getStringToMinify(): string
    {
        return <<<'EOT'
<?php

namespace Test;

class Foo
{
    /**
     * Comment.
     */
    public function bar(int $value): int
    {
        return $value ** $value;
    }
}
EOT;
    }

    /**
     * Get the expected minification result.
     */
    private function getExpectedMinifiedString(): string
    {
        return <<<'EOT'
<?php
 namespace Test; class Foo { public function bar(int $value): int { return $value ** $value; } }
EOT;
    }
}
