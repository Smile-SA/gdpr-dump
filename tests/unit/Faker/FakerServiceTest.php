<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Faker;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Faker\FakerService;
use Smile\GdprDump\Tests\Unit\TestCase;

class FakerServiceTest extends TestCase
{
    /**
     * Test the "getGenerator" method.
     */
    public function testGenerator(): void
    {
        $fakerService = new FakerService($this->createMock(Config::class));

        $generator = $fakerService->getGenerator();
        $this->assertNotEmpty($generator->getProviders());
    }

    /**
     * Test the "getGenerator" looks for the configured locale
     */
    public function testGeneratorUsesLocaleConfiguration(): void
    {
        $configMock = $this->createMock(Config::class);
        $configMock
            ->expects($this->once())
            ->method('get')
            ->with('faker')
            ->willReturn(null);

        $fakerService = new FakerService($configMock);

        $generator = $fakerService->getGenerator();
        $this->assertNotEmpty($generator->getProviders());
    }

    /**
     * Test the "getGenerator" works with a locale configured
     */
    public function testGeneratorWithLocaleConfigured(): void
    {
        $configMock = $this->createMock(Config::class);
        $configMock
            ->expects($this->once())
            ->method('get')
            ->with('faker')
            ->willReturn([
                'locale' => 'de_DE'
            ]);

        $fakerService = new FakerService($configMock);

        $generator = $fakerService->getGenerator();
        $this->assertNotEmpty($generator->getProviders());
    }
}
