<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Loader\Version;

use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Configuration\Loader\Env\EnvVarParser;
use Smile\GdprDump\Configuration\Loader\Version\VersionApplier;
use Smile\GdprDump\Tests\Unit\TestCase;
use stdClass;

final class VersionApplierTest extends TestCase
{
    /**
     * Test version detection.
     */
    public function testDetectVersion(): void
    {
        $version = $this->createVersionApplier()->detectVersion((object) ['version' => '2.0.0']);
        $this->assertSame('2.0.0', $version);
    }

    /**
     * Assert that "if_version" blocks are processed successfully.
     */
    public function testApplyVersion(): void
    {
        $data = (object) [
            'tables' => (object) ['key' => 'value'],
            'if_version' => (object) [
                '>=1.0.0 <2.0.0' => (object) [
                    'variables' => (object) ['new_key' => 1],
                ],
                '>=2.0.0' => (object) [
                    'tables' => (object) ['key' => 'new_value'],
                ],
            ],
        ];

        // Test with version "1.0.0"
        $processedData = $this->processVersions($data, '1.0.0');
        $this->assertSame('value', $processedData->tables->key);
        $this->assertObjectHasProperty('variables', $processedData);
        $this->assertObjectHasProperty('new_key', $processedData->variables);
        $this->assertSame(1, $processedData->variables->new_key);

        // Test with version "2.0.0"
        $processedData = $this->processVersions($data, '2.0.0');
        $this->assertSame('new_value', $processedData->tables->key);
        $this->assertObjectNotHasProperty('variables', $processedData);
    }

    /**
     * Assert that an exception is thrown when the version is an empty string.
     */
    public function testEmptyVersion(): void
    {
        $this->expectException(ParseException::class);
        $this->createVersionApplier()->detectVersion((object) ['version' => '']);
    }

    /**
     * Assert that an exception is thrown when the version has an invalid type.
     */
    public function testInvalidVersionType(): void
    {
        $this->expectException(ParseException::class);
        $this->createVersionApplier()->detectVersion((object) ['version' => ['not a string']]);
    }

    /**
     * Assert that an exception is thrown when the version data has an invalid type.
     */
    public function testInvalidVersionsData(): void
    {
        $this->expectException(ParseException::class);
        $this->processVersions((object) ['if_version' => 'not an array'], '1.0.0');
    }

    // /**
    //  * Assert that an exception is thrown when the version data contains a disallowed parameter.
    //  */
    // public function testDisallowedParameterInsideVersionData(): void
    // {
    //     $this->expectException(ParseException::class);
    //     $this->processVersions((object) [
    //         'if_version' => (object) [
    //             '>2.0.0' => (object) [
    //                 'not_allowed' => (object) ['key' => 'value']
    //             ],
    //         ],
    //     ], '1.0.0');
    // }

    /**
     * Create and run the event listener with the specified configuration.
     */
    private function processVersions(stdClass $dataObject, string $version): stdClass
    {
        $dataObject = clone $dataObject; // for easier comparison in assertions
        $this->createVersionApplier()
            ->applyVersion($dataObject, $version);

        return $dataObject;
    }

    /**
     * Create the object to test.
     */
    private function createVersionApplier(): VersionApplier
    {
        return new VersionApplier(new EnvVarParser());
    }
}
