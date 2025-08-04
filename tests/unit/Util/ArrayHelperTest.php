<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Util;

use RuntimeException;
use Smile\GdprDump\Tests\Unit\TestCase;
use Smile\GdprDump\Util\ArrayHelper;

final class ArrayHelperTest extends TestCase
{
    /**
     * Test the "getPath" method.
     */
    public function testGetPath(): void
    {
        $data = ['customer' => ['email' => 'email@example.org']];
        $arrayHelper = new ArrayHelper();

        $this->assertSame('email@example.org', $arrayHelper->getPath($data, 'customer.email'));
        $this->assertNull($arrayHelper->getPath($data, 'not_exists'));
        $this->assertSame('defaultValue', $arrayHelper->getPath($data, 'not_exists', 'defaultValue'));
    }

    /**
     * Test the "setPath" method.
     */
    public function testSetPath(): void
    {
        $data = [];
        $arrayHelper = new ArrayHelper();
        $arrayHelper->setPath($data, 'customer.email', 'email@example.org');

        $this->assertArrayHasKey('customer', $data);
        $this->assertArrayHasKey('email', $data['customer']);
        $this->assertSame('email@example.org', $data['customer']['email']);
    }

    /**
     * Test the "map" method.
     */
    public function testMap(): void
    {
        $data = ['k11' => 'v1', 'k22' => 'v2', 'k3' => 'v3'];
        $arrayHelper = new ArrayHelper();
        $result = $arrayHelper->map($data, ['k11' => 'k1', 'k22' => 'k2', 'k3' => 'k3']);

        $data['k1'] = $data['k11'];
        $data['k2'] = $data['k22'];
        unset($data['k11']);
        unset($data['k22']);
        $this->assertSameKeyValuePairs($data, $result);
    }

    /**
     * Assert that an exception is thrown when the input array contains a property that is not defined in the mapping.
     */
    public function testPropertyNotInMapping(): void
    {
        $data = ['k1' => 'v1', 'undefined' => 'v2'];
        $arrayHelper = new ArrayHelper();

        $this->expectException(RuntimeException::class);
        $arrayHelper->map($data, ['k1' => 'k11', 'k2' => 'k22']);
    }
}
