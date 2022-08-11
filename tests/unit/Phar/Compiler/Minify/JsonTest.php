<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Phar\Compiler\Minify;

use Smile\GdprDump\Phar\Minify\Json;
use Smile\GdprDump\Tests\Unit\TestCase;

class JsonTest extends TestCase
{
    /**
     * Test the "minify" method.
     */
    public function testMinify(): void
    {
        $minifier = new Json();
        $this->assertSame($this->getExpectedMinifiedString(), $minifier->minify($this->getStringToMinify()));
    }

    /**
     * Test the "supports" method.
     */
    public function testSupports(): void
    {
        $minifier = new Json();
        $this->assertTrue($minifier->supports('json'));
        $this->assertFalse($minifier->supports('php'));
        $this->assertFalse($minifier->supports(''));
    }

    /**
     * Get the string to minify.
     */
    private function getStringToMinify(): string
    {
        return <<<'EOT'
{
    "object": {
        "unicode": "⚡🗲↯ϟ",
        "int": 2,
        "float": 2.5,
        "bool": true
    },
    "array": [1, 2],
    "slash": "/foo/bar",
    "backslash": "Foo\\Bar"
}
EOT;
    }

    /**
     * Get the expected minification result.
     */
    private function getExpectedMinifiedString(): string
    {
        return <<<'EOT'
{"object":{"unicode":"⚡🗲↯ϟ","int":2,"float":2.5,"bool":true},"array":[1,2],"slash":"/foo/bar","backslash":"Foo\\Bar"}
EOT;
    }
}
