<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Processor;

use Smile\GdprDump\Config\Processor\EnvVarProcessor;
use Smile\GdprDump\Config\Processor\ProcessException;
use Smile\GdprDump\Tests\Unit\TestCase;

class EnvVarProcessorTest extends TestCase
{
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function tearDown(): void
    {
        unset($_SERVER['TEST_ENV_VAR']);
    }

    /**
     * Assert that normal values are not processed.
     */
    public function testNormalValuesNotProcessed(): void
    {
        $processor = new EnvVarProcessor();

        $value = $processor->process('12345');
        $this->assertSame('12345', $value);

        $value = $processor->process('env(TEST_ENV_VAR)');
        $this->assertSame('env(TEST_ENV_VAR)', $value);
    }

    /**
     * Assert that scalar environment variables are processed successfully.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testScalarEnvVar(): void
    {
        $processor = new EnvVarProcessor();
        $_SERVER['TEST_ENV_VAR'] = '12345';

        $value = $processor->process('%env(TEST_ENV_VAR)%');
        $this->assertSame($_SERVER['TEST_ENV_VAR'], $value);

        $value = $processor->process('%env(string:TEST_ENV_VAR)%');
        $this->assertSame($_SERVER['TEST_ENV_VAR'], $value);

        $value = $processor->process('%env(bool:TEST_ENV_VAR)%');
        $this->assertSame(true, $value);

        $value = $processor->process('%env(int:TEST_ENV_VAR)%');
        $this->assertSame(12345, $value);

        $value = $processor->process('%env(float:TEST_ENV_VAR)%');
        $this->assertSame(12345.0, $value);
    }

    /**
     * Assert that array environment variables are processed successfully.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testJsonEnvVar(): void
    {
        $processor = new EnvVarProcessor();
        $_SERVER['TEST_ENV_VAR'] = '{"key": "value"}';

        $value = $processor->process('%env(json:TEST_ENV_VAR)%');
        $this->assertSame(['key' => 'value'], $value);
    }

    /**
     * Assert that an exception is thrown when the JSON data is invalid.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testInvalidJson(): void
    {
        $processor = new EnvVarProcessor();
        $_SERVER['TEST_ENV_VAR'] = 'invalidData';

        $this->expectException(ProcessException::class);
        $processor->process('%env(json:TEST_ENV_VAR)%');
    }

    /**
     * Assert that an exception is thrown when the environment variable is not defined.
     */
    public function testUndefinedEnvVar(): void
    {
        $processor = new EnvVarProcessor();

        $this->expectException(ProcessException::class);
        $processor->process('%env(NOT_DEFINED)%');
    }

    /**
     * Assert that an exception is thrown when the environment variable name is not specified.
     */
    public function testEmptyVariableName(): void
    {
        $processor = new EnvVarProcessor();

        $this->expectException(ProcessException::class);
        $processor->process('%env()%');
    }

    /**
     * Assert that an exception is thrown when the environment variable name and type are not specified.
     */
    public function testEmptyVariableNameAndType(): void
    {
        $processor = new EnvVarProcessor();

        $this->expectException(ProcessException::class);
        $processor->process('%env(:)%');
    }

    /**
     * Assert that an exception is thrown when the environment variable type is not specified.
     */
    public function testEmptyVariableType(): void
    {
        $processor = new EnvVarProcessor();

        $this->expectException(ProcessException::class);
        $processor->process('%env(:ENV_VAR)%');
    }

    /**
     * Assert that an exception is thrown when the environment variable name is invalid.
     */
    public function testInvalidVariableName(): void
    {
        $processor = new EnvVarProcessor();

        $this->expectException(ProcessException::class);
        $processor->process('%env(invalidName)%');
    }

    /**
     * Assert that an exception is thrown when the environment variable type is invalid.
     */
    public function testInvalidVariableType(): void
    {
        $processor = new EnvVarProcessor();

        $this->expectException(ProcessException::class);
        $processor->process('%env(invalidType:ENV_VAR)%');
    }
}
