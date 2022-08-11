<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Compiler\Processor;

use Smile\GdprDump\Config\Compiler\CompileException;
use Smile\GdprDump\Config\Compiler\Processor\EnvVarProcessor;
use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Tests\Unit\TestCase;

class EnvVarProcessorTest extends TestCase
{
    /**
     * Assert that environment variables are processed successfully.
     */
    public function testEnvVarProcessor(): void
    {
        $data = [
            'no_type' => '%env(TEST_ENV_VAR)%',
            'string' => '%env(string:TEST_ENV_VAR)%',
            'bool' => '%env(bool:TEST_ENV_VAR)%',
            'int' => '%env(int:TEST_ENV_VAR)%',
            'float' => '%env(float:TEST_ENV_VAR)%',
            'json' => '%env(json:TEST_ENV_VAR)%',
        ];

        $config = new Config($data);
        $processor = new EnvVarProcessor();
        putenv('TEST_ENV_VAR=12345');
        $processor->process($config);

        $env = getenv('TEST_ENV_VAR');

        $this->assertSame($env, $config->get('no_type'));
        $this->assertSame((string) $env, $config->get('string'));
        $this->assertSame((bool) $env, $config->get('bool'));
        $this->assertSame((int) $env, $config->get('int'));
        $this->assertSame((float) $env, $config->get('float'));

        putenv('TEST_ENV_VAR');
    }

    /**
     * Assert that array environment variables are processed successfully.
     */
    public function testJsonEnvVar(): void
    {
        $config = new Config(['json' => '%env(json:TEST_ENV_VAR)%']);
        $processor = new EnvVarProcessor();
        putenv('TEST_ENV_VAR={"key": "value"}');
        $processor->process($config);

        $this->assertSame(['key' => 'value'], $config->get('json'));

        putenv('TEST_ENV_VAR');
    }

    /**
     * Assert that normal values are not processed.
     */
    public function testNormalValuesNotProcessed(): void
    {
        $data = ['key1' => '12345', 'key2' => 'env(test)'];
        $config = new Config($data);

        $processor = new EnvVarProcessor();
        $processor->process($config);

        $this->assertSame($data['key1'], $config->get('key1'));
        $this->assertSame($data['key2'], $config->get('key2'));
    }

    /**
     * Assert that an exception is thrown when the JSON data is invalid.
     */
    public function testInvalidJson(): void
    {
        putenv('TEST_ENV_VAR=invalidData');

        $this->expectException(CompileException::class);
        $this->processValue('%env(json:TEST_ENV_VAR)%');

        putenv('TEST_ENV_VAR');
    }

    /**
     * Assert that an exception is thrown when the environment variable is not defined.
     */
    public function testUndefinedEnvVar(): void
    {
        $this->expectException(CompileException::class);
        $this->processValue('%env(NOT_DEFINED)%');
    }

    /**
     * Assert that an exception is thrown when the environment variable name is not specified.
     */
    public function testEmptyVariableName(): void
    {
        $this->expectException(CompileException::class);
        $this->processValue('%env()%');
    }

    /**
     * Assert that an exception is thrown when the environment variable name and type are not specified.
     */
    public function testEmptyVariableNameAndType(): void
    {
        $this->expectException(CompileException::class);
        $this->processValue('%env(:)%');
    }

    /**
     * Assert that an exception is thrown when the environment variable type is not specified.
     */
    public function testEmptyVariableType(): void
    {
        $this->expectException(CompileException::class);
        $this->processValue('%env(:ENV_VAR)%');
    }

    /**
     * Assert that an exception is thrown when the environment variable name is invalid.
     */
    public function testInvalidVariableName(): void
    {
        $this->expectException(CompileException::class);
        $this->processValue('%env(invalidName)%');
    }

    /**
     * Assert that an exception is thrown when the environment variable type is invalid.
     */
    public function testInvalidVariableType(): void
    {
        $this->expectException(CompileException::class);
        $this->processValue('%env(invalidType:ENV_VAR)%');
    }

    /**
     * Process the specified value.
     */
    private function processValue(string $value): void
    {
        $processor = new EnvVarProcessor();
        $config = new Config([$value]);
        $processor->process($config);
    }
}
