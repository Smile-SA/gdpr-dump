<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Config\Validator;

use Smile\GdprDump\Config\Validator\ValidationResult;
use Smile\GdprDump\Tests\TestCase;

class ValidationResultTest extends TestCase
{
    /**
     * Test the "valid" property.
     */
    public function testValidProperty()
    {
        $result = new ValidationResult();

        // Default value
        $this->assertFalse($result->isValid());

        // Setter
        $result->setValid(true);
        $this->assertTrue($result->isValid());
    }

    /**
     * Test the "messages" property.
     */
    public function testMessagesProperty()
    {
        $result = new ValidationResult();

        // Default values
        $this->assertEmpty($result->getMessages());

        // Setter
        $messages = ['message1', 'message2'];
        $result->setMessages($messages);
        $this->assertSame($messages, $result->getMessages());
    }
}
