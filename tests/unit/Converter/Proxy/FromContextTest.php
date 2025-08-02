<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Proxy\FromContext;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

final class FromContextTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        // Test row_data
        $converter = $this->createConverter(FromContext::class, ['key' => 'row_data.email']);
        $this->dumpContext->currentRow = ['email' => 'test@acme.com'];
        $value = $converter->convert('value');
        $this->assertSame($this->dumpContext->currentRow['email'], $value);

        // Test processed_data
        $converter = $this->createConverter(FromContext::class, ['key' => 'processed_data.email']);
        $this->dumpContext->processedData = ['email' => '1234@example.org'];
        $value = $converter->convert('value');
        $this->assertSame($this->dumpContext->processedData['email'], $value);

        // Test variables
        $converter = $this->createConverter(FromContext::class, ['key' => 'variables.firstname_attribute_id']);
        $this->dumpContext->variables = ['firstname_attribute_id' => '1'];
        $value = $converter->convert('value');
        $this->assertSame($this->dumpContext->variables['firstname_attribute_id'], $value);
    }

    /**
     * Assert that the converter returns null when the specified column (e.g. email) is not defined.
     */
    public function testReturnsNullWithUndefinedColumn(): void
    {
        // Test row_data
        $converter = $this->createConverter(FromContext::class, ['key' => 'row_data.email']);
        $this->dumpContext->currentRow = ['username' => 'test'];
        $value = $converter->convert('value');
        $this->assertNull($value);

        // Test processed_data
        $converter = $this->createConverter(FromContext::class, ['key' => 'processed_data.email']);
        $this->dumpContext->processedData = ['username' => 'test'];
        $value = $converter->convert('value');
        $this->assertNull($value);

        // Test variables
        $converter = $this->createConverter(FromContext::class, ['key' => 'variables.firstname_attribute_id']);
        $this->dumpContext->variables = ['lastname_attribute_id' => '1'];
        $value = $converter->convert('value');
        $this->assertNull($value);
    }

    /**
     * Assert that an exception is thrown when the parameter "key" is not set.
     */
    public function testKeyNotSet(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(FromContext::class);
    }

    /**
     * Assert that an exception is thrown when the parameter "key" is empty.
     */
    public function testEmptyKey(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(FromContext::class, ['key' => '']);
    }

    /**
     * Assert that an exception is thrown when the first part of the key is invalid.
     */
    public function testInvalidContextType(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(FromContext::class, ['key' => 'foo.email']);
    }

    /**
     * Assert that an exception is thrown when the key contains less than 2 parts.
     */
    public function testKeyHasTooFewParts(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(FromContext::class, ['key' => 'row_data']);
    }

    /**
     * Assert that an exception is thrown when the key contains more than 2 parts.
     */
    public function testKeyHasTooManyParts(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(FromContext::class, ['key' => 'row_data.foo.bar']);
    }
}
