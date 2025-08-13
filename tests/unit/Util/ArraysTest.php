<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Util;

use Smile\GdprDump\Tests\Unit\TestCase;
use Smile\GdprDump\Util\Arrays;

final class ArraysTest extends TestCase
{
    /**
     * Test the "getPath" method.
     */
    public function testGetPath(): void
    {
        $data = ['customer' => ['email' => 'email@example.org']];

        $this->assertSame('email@example.org', Arrays::getPath($data, 'customer.email'));
        $this->assertNull(Arrays::getPath($data, 'not_exists'));
        $this->assertSame('defaultValue', Arrays::getPath($data, 'not_exists', 'defaultValue'));
    }

    /**
     * Test the "setPath" method.
     */
    public function testSetPath(): void
    {
        $data = [];
        Arrays::setPath($data, 'customer.email', 'email@example.org');

        $this->assertArrayHasKey('customer', $data);
        $this->assertArrayHasKey('email', $data['customer']);
        $this->assertSame('email@example.org', $data['customer']['email']);
    }

    /**
     * Test the "map" method.
     */
    public function testMap(): void
    {
        // phpcs:ignore SlevomatCodingStandard.Arrays.DisallowPartiallyKeyed
        $data = [
            'index0',
            'index1',
            'index2',
            'k1' => 'v1',
            'k2' => 'v2',
            'k3' => 'v3',
            'k4' => 'v4',
            'k5' => 'v5',
            'k6' => 'v6',
        ];

        $result = Arrays::mapKeys($data, function (int|string $key) {
            return match ($key) {
                'k1' => 'k2', // k1 renamed to k2, this will override k2
                'k3' => 'k1', // k3 rename to k2
                'k4', 0 => false, // index 0 and k4 removed
                'k6' => 3, // k6 moved to index 3
                1 => 'k0', // index 1 renamed to k0
                default => $key, // index 2 and k5 preserved
            };
        });

        $expected = [
            'k0' => $data[1],
            'k1' => $data['k3'],
            'k2' => $data['k1'],
            'k5' => $data['k5'],
            2 => 'index2',
            3 => $data['k6'],
        ];

        $this->assertSameKeyValuePairs($expected, $result);
    }
}
