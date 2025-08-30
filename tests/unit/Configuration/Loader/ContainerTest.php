<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration;

use Smile\GdprDump\Configuration\Loader\Container;
use Smile\GdprDump\Tests\Unit\TestCase;
use Smile\GdprDump\Util\Objects;
use stdClass;

final class ContainerTest extends TestCase
{
    /**
     * Test the object creation.
     */
    public function testContainer(): void
    {
        $data = (object) [
            'tables' => [
                'table1' => (object) [
                    'truncate' => true,
                ],
            ],
        ];

        $container = new Container($data);
        $this->assertTrue($container->has('tables'));
        $this->assertEquals($data->tables, $container->get('tables'));
        $this->assertEquals($data, $container->getRoot());
        $this->assertSame(Objects::toArray($data), $container->toArray());

        $container->set('strict_schema', false);
        $this->assertTrue($container->has('strict_schema'));
        $this->assertFalse($container->get('strict_schema'));
    }

    /**
     * Test the container methods when it is empty.
     */
    public function testEmptyContainer(): void
    {
        $container = new Container();
        $this->assertFalse($container->has('field'));
        $this->assertNull($container->get('field'));
        $this->assertEquals(new stdClass(), $container->getRoot());
        $this->assertSame([], $container->toArray());
    }
}
