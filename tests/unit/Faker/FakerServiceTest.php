<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Faker;

use Faker\Generator;
use Smile\GdprDump\Faker\FakerService;
use Smile\GdprDump\Tests\Unit\TestCase;

class FakerServiceTest extends TestCase
{
    /**
     * Test the "getGenerator" method.
     */
    public function testGenerator()
    {
        $fakerService = new FakerService();

        $generator = $fakerService->getGenerator();
        $this->assertNotEmpty($generator->getProviders());
    }
}
