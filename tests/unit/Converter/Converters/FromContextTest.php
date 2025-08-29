<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Converters;

use Smile\GdprDump\Converter\Converters\FromContext;
use Smile\GdprDump\Converter\Exception\InvalidParameterException;
use Smile\GdprDump\Tests\Unit\Converter\DumpContextAware;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

final class FromContextTest extends TestCase implements DumpContextAware
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $dumpContext = $this->getDumpContext();

        // Test row_data
        $converter = $this->createConverter(FromContext::class, ['key' => 'row_data.email']);
        $dumpContext->currentRow = ['email' => 'test@acme.com'];
        $value = $converter->convert('value');
        $this->assertSame($dumpContext->currentRow['email'], $value);

        // Test processed_data
        $converter = $this->createConverter(FromContext::class, ['key' => 'processed_data.email']);
        $dumpContext->processedData = ['email' => '1234@acme.com'];
        $value = $converter->convert('value');
        $this->assertSame($dumpContext->processedData['email'], $value);

        // Test variables
        $converter = $this->createConverter(FromContext::class, ['key' => 'variables.firstname_attribute_id']);
        $dumpContext->variables = ['firstname_attribute_id' => '1'];
        $value = $converter->convert('value');
        $this->assertSame($dumpContext->variables['firstname_attribute_id'], $value);
    }

    /**
     * Assert that the converter returns null when the specified column (e.g. email) is not defined.
     */
    public function testReturnsNullWithUndefinedColumn(): void
    {
        $dumpContext = $this->getDumpContext();

        // Test row_data
        $converter = $this->createConverter(FromContext::class, ['key' => 'row_data.email']);
        $dumpContext->currentRow = ['username' => 'test'];
        $value = $converter->convert('value');
        $this->assertNull($value);

        // Test processed_data
        $converter = $this->createConverter(FromContext::class, ['key' => 'processed_data.email']);
        $dumpContext->processedData = ['username' => 'test'];
        $value = $converter->convert('value');
        $this->assertNull($value);

        // Test variables
        $converter = $this->createConverter(FromContext::class, ['key' => 'variables.firstname_attribute_id']);
        $dumpContext->variables = ['lastname_attribute_id' => '1'];
        $value = $converter->convert('value');
        $this->assertNull($value);
    }

    /**
     * Assert that an exception is thrown when the parameter "key" is not set.
     */
    public function testKeyNotSet(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(FromContext::class);
    }

    /**
     * Assert that an exception is thrown when the parameter "key" is empty.
     */
    public function testEmptyKey(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(FromContext::class, ['key' => '']);
    }

    /**
     * Assert that an exception is thrown when the first part of the key is invalid.
     */
    public function testInvalidContextType(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(FromContext::class, ['key' => 'foo.email']);
    }

    /**
     * Assert that an exception is thrown when the key contains less than 2 parts.
     */
    public function testKeyHasTooFewParts(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(FromContext::class, ['key' => 'row_data']);
    }

    /**
     * Assert that an exception is thrown when the key contains more than 2 parts.
     */
    public function testKeyHasTooManyParts(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(FromContext::class, ['key' => 'row_data.foo.bar']);
    }
}
