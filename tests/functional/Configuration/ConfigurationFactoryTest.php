<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration;

use Smile\GdprDump\Configuration\ConfigurationFactory;
use Smile\GdprDump\Configuration\Exception\FileNotFoundException;
use Smile\GdprDump\Tests\Functional\TestCase;

final class ConfigurationFactoryTest extends TestCase
{
    /**
     * Test builder creation.
     */
    public function testCreateBuilder(): void
    {
        $factory = $this->createFactory();

        // Test that the factory does not return a singleton
        $this->assertNotSame($factory->createBuilder(), $factory->createBuilder());
    }

    /**
     * Test the creation of a file resource.
     */
    public function testCreateFileResource(): void
    {
        $resource = $this->createFactory()->createFileResource('magento2');
        $this->assertStringContainsString('app/config/templates/magento2.yaml', $resource->getInput());
        $this->assertTrue($resource->isFile());
    }

    /**
     * Test the creation of a string resource.
     */
    public function testCreateStringResource(): void
    {
        $resource = $this->createFactory()->createStringResource('{}');
        $this->assertSame('{}', $resource->getInput());
        $this->assertFalse($resource->isFile());
    }

    /**
     * Assert that an exception is thrown when trying to create a file resource that does not exist.
     */
    public function testFileResourcePathIsValidated(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->createFactory()->createFileResource('not_exists.yaml');
    }

    /**
     * Create a converter factory.
     */
    private function createFactory(): ConfigurationFactory
    {
        /** @var ConfigurationFactory */
        return $this->getContainer()->get(ConfigurationFactory::class);
    }
}
