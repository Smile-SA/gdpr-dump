<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Converter\Helper;

use Smile\Anonymizer\Converter\Helper\ArrayHelper;
use Smile\Anonymizer\Tests\TestCase;

class ArrayHelperTest extends TestCase
{
    /**
     * Test the "getPath" method.
     */
    public function testGetPath()
    {
        $data = [
            'customer' => [
                'email' => 'email@example.org',
            ]
        ];

        $this->assertSame('email@example.org', ArrayHelper::getPath($data, 'customer.email'));
        $this->assertNull(ArrayHelper::getPath($data, 'notExists'));
        $this->assertSame('defaultValue', ArrayHelper::getPath($data, 'notExists', 'defaultValue'));
    }

    /**
     * Test the "setPath" method.
     */
    public function testSetPath()
    {
        $data = [];

        ArrayHelper::setPath($data, 'customer.email', 'email@example.org');
        $this->assertSame('email@example.org', $data['customer']['email']);
    }
}
