<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Validator\Schema;

use Smile\GdprDump\Configuration\Validator\Schema\ValidationResult;
use Smile\GdprDump\Tests\Unit\TestCase;

final class ValidationResultTest extends TestCase
{
    /**
     * Test the object creation and setters.
     */
    public function testObjectCreation(): void
    {
        $result = new ValidationResult(true, []);
        $this->assertTrue($result->isValid());
        $this->assertSame([], $result->getMessages());

        $messages = ['message1'];
        $result = new ValidationResult(false, $messages);
        $this->assertFalse($result->isValid());
        $this->assertSame($messages, $result->getMessages());
    }
}
