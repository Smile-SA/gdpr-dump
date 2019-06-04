<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Faker;

use Faker\Generator;
use Smile\GdprDump\Faker\FakerService;
use Smile\GdprDump\Tests\TestCase;

class FakerServiceTest extends TestCase
{
    /**
     * Test the "getGenerator" method.
     */
    public function testGenerator()
    {
        $fakerService = new FakerService();

        $generator = $fakerService->getGenerator();
        $this->assertInstanceOf(Generator::class, $generator);
    }
}
