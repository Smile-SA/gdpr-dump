<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Proxy\Internal\Conditional;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Smile\GdprDump\Tests\Unit\Converter\DumpContextAwareInterface;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;
use stdClass;

final class ConditionalTest extends TestCase implements DumpContextAwareInterface
{
    /**
     * Test the converter.
     */
    public function testCondition(): void
    {
        $dumpContext = $this->getDumpContext();
        $converter = $this->createConverter(Conditional::class, [
            'condition' => '{{id}} === @my_var',
            'converter' => $this->createOnSuccessConverter(),
        ]);

        $dumpContext->currentRow = ['id' => '1'];
        $dumpContext->variables = ['my_var' => '1'];
        $value = $converter->convert('value');
        $this->assertSame('success_value', $value);

        $dumpContext->variables = ['my_var' => '2'];
        $value = $converter->convert('value');
        $this->assertSame('value', $value);

        $dumpContext->currentRow = ['id' => '2'];
        $dumpContext->variables = ['my_var' => '1'];
        $value = $converter->convert('value');
        $this->assertSame('value', $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "converter" is not set.
     */
    public function testConverterNotSet(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(Conditional::class, ['condition' => '{{id}} === 1']);
    }

    /**
     * Assert that an exception is thrown when the parameter "converter" is not an instance of ConverterInterface.
     */
    public function testConverterNotValid(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(Conditional::class, ['converter' => new stdClass()]);
    }

    /**
     * Assert that an exception is thrown when the parameter "condition" is not set.
     */
    public function testConditionNotSet(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(Conditional::class, ['converter' => $this->createOnSuccessConverter()]);
    }

    /**
     * Assert that an exception is thrown when the parameter "condition" is empty.
     */
    public function testEmptyCondition(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(Conditional::class, [
            'converter' => $this->createOnSuccessConverter(),
            'condition' => '',
        ]);
    }

    /**
     * Create a test converter for conditions that evaluate to true.
     */
    private function createOnSuccessConverter(): ConverterInterface
    {
        return $this->createConverter(ConverterMock::class, ['prefix' => 'success_']);
    }
}
