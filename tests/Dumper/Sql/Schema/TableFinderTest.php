<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Dumper\Schema;

use Smile\Anonymizer\Dumper\Sql\Schema\TableFinder;
use Smile\Anonymizer\Tests\DatabaseTestCase;

class TableFinderTest extends DatabaseTestCase
{
    /**
     * Test if a table is found by name.
     */
    public function testFindByName()
    {
        $tableFinder = new TableFinder($this->getConnection());
        $expectedMatch = ['customers'];

        // Exact match
        $this->assertSame($expectedMatch, $tableFinder->findByName('customers'));

        // Pattern
        $this->assertSame($expectedMatch, $tableFinder->findByName('cust*'));
    }
}
