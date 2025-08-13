<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Loader;

use Smile\GdprDump\Configuration\Exception\EnvVarException;
use Smile\GdprDump\Configuration\Loader\EnvVarProcessor;
use Smile\GdprDump\Tests\Unit\TestCase;

final class EnvVarProcessorTest extends TestCase
{
    /**
     * Assert that scalar are successfully processed.
     */
    public function testProcessScalarValue(): void
    {
        putenv('TEST_VAR=1234');

        $processed = (new EnvVarProcessor())->process('%env(TEST_VAR)%');
        $this->assertSame(getenv('TEST_VAR'), $processed);

        putenv('TEST_VAR');
    }

    /**
     * Assert that arrays are successfully processed.
     */
    public function testProcessArrayValue(): void
    {
        putenv('TEST_VAR=1234');

        $processed = (new EnvVarProcessor())->process(['key' => '%env(TEST_VAR)%']);
        $this->assertIsArray($processed);
        $this->assertArrayHasKey('key', $processed);
        $this->assertSame(getenv('TEST_VAR'), $processed['key']);

        putenv('TEST_VAR');
    }

    /**
     * Assert that objects are successfully processed.
     */
    public function testProcessObjectValue(): void
    {
        putenv('TEST_VAR=1234');

        $processed = (new EnvVarProcessor())->process((object) ['key' => '%env(TEST_VAR)%']);
        $this->assertIsObject($processed);
        $this->assertObjectHasProperty('key', $processed);
        $this->assertSame(getenv('TEST_VAR'), $processed->key);

        putenv('TEST_VAR');
    }

    /**
     * Assert that env vars can be cast to the specified type.
     */
    public function testTypeCasting(): void
    {
        $data = [
            'string' => '%env(string:TEST_VAR)%',
            'bool' => '%env(bool:TEST_VAR)%',
            'int' => '%env(int:TEST_VAR)%',
            'float' => '%env(float:TEST_VAR)%',
        ];

        putenv('TEST_VAR=1234');

        $envVar = getenv('TEST_VAR');
        $processed = (new EnvVarProcessor())->process($data);
        $this->assertIsArray($processed);
        $this->assertSame([
            'string' => (string) $envVar,
            'bool' => (bool) $envVar,
            'int' => (int) $envVar,
            'float' => (float) $envVar,
        ], $processed);

        putenv('TEST_VAR');
    }

    /**
     * Assert that env vars can be decoded from JSON.
     */
    public function testJsonEnvVar(): void
    {
        putenv('TEST_VAR={"foo": "bar"}');

        $envVar = getenv('TEST_VAR');
        $processed = (new EnvVarProcessor())->process('%env(json:TEST_VAR)%');
        $this->assertIsObject($processed);
        $this->assertEquals(json_decode($envVar), $processed);

        putenv('TEST_VAR');
    }


    /**
     * Assert that normal values are not processed.
     */
    public function testNonPlaceholdersNotProcessed(): void
    {
        $data = [
            '12345',
            'env(test',
            'env(test)',
            'env(test)%',
            '%env(test',
            '%env(test)',
            '%env(test%',
        ];
        $processed = (new EnvVarProcessor())->process($data);
        $this->assertSame($data, $processed);
    }

    /**
     * Assert that an exception is thrown when the JSON data is invalid.
     */
    public function testInvalidJson(): void
    {
        putenv('TEST_VAR=invalidData');

        $this->expectException(EnvVarException::class);
        (new EnvVarProcessor())->process('%env(json:TEST_VAR)%');

        putenv('TEST_VAR');
    }

    /**
     * Assert that an exception is thrown when the environment variable is not defined.
     */
    public function testUndefinedEnvVar(): void
    {
        $this->expectException(EnvVarException::class);
        (new EnvVarProcessor())->process('%env(NOT_DEFINED)%');
    }

    /**
     * Assert that an exception is thrown when the environment variable name is not specified.
     */
    public function testEmptyVariableName(): void
    {
        $this->expectException(EnvVarException::class);
        (new EnvVarProcessor())->process('%env()%');
    }

    /**
     * Assert that an exception is thrown when the environment variable name and type are not specified.
     */
    public function testEmptyVariableNameAndType(): void
    {
        $this->expectException(EnvVarException::class);
        (new EnvVarProcessor())->process('%env(:)%');
    }

    /**
     * Assert that an exception is thrown when the environment variable type is not specified.
     */
    public function testEmptyVariableType(): void
    {
        $this->expectException(EnvVarException::class);
        (new EnvVarProcessor())->process('%env(:ENV_VAR)%');
    }

    /**
     * Assert that an exception is thrown when the environment variable name is invalid.
     */
    public function testInvalidVariableName(): void
    {
        $this->expectException(EnvVarException::class);
        (new EnvVarProcessor())->process('%env(invalidName)%');
    }

    /**
     * Assert that an exception is thrown when the environment variable type is invalid.
     */
    public function testInvalidVariableType(): void
    {
        $this->expectException(EnvVarException::class);
        (new EnvVarProcessor())->process('%env(invalidType:ENV_VAR)%');
    }
}
