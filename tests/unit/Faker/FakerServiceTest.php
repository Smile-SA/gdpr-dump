<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Faker;

use Faker\Factory;
use Smile\GdprDump\Faker\FakerService;
use Smile\GdprDump\Tests\Unit\TestCase;

class FakerServiceTest extends TestCase
{
    /**
     * Test the "getGenerator" method.
     */
    public function testGenerator(): void
    {
        $fakerService = new FakerService();

        $generator = $fakerService->getGenerator();
        $this->assertNotEmpty($generator->getProviders());
        $this->assertSame(Factory::DEFAULT_LOCALE, $fakerService->getLocale());
    }

    /**
     * Test the "getGenerator" method with a custom locale.
     */
    public function testGeneratorWithCustomLocale(): void
    {
        $fakerService = new FakerService();
        $generator = $fakerService->getGenerator();

        $fakerService->setLocale('ru_RU');
        $this->assertSame('ru_RU', $fakerService->getLocale());
        $this->assertNotSame($generator, $fakerService->getGenerator());
    }
}
