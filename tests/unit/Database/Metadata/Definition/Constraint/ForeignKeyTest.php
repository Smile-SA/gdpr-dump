<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Database\Metadata\Definition\Constraint;

use Smile\GdprDump\Database\Metadata\Definition\Constraint\ForeignKey;
use Smile\GdprDump\Tests\Unit\TestCase;

class ForeignKeyTest extends TestCase
{
    /**
     * Test the getters methods.
     */
    public function testGetters(): void
    {
        [$constraintName, $localTable, $localColumns, $foreignTable, $foreignColumns] = $this->getForeignKeyData();

        $foreignKey = new ForeignKey(
            $constraintName,
            $localTable,
            $localColumns,
            $foreignTable,
            $foreignColumns
        );

        $this->assertSame($constraintName, $foreignKey->getConstraintName());
        $this->assertSame($localTable, $foreignKey->getLocalTableName());
        $this->assertSame($localColumns, $foreignKey->getLocalColumns());
        $this->assertSame($foreignTable, $foreignKey->getForeignTableName());
        $this->assertSame($foreignColumns, $foreignKey->getForeignColumns());
    }

    /**
     * Get fake foreign key data.
     */
    private function getForeignKeyData(): array
    {
        return [
            'fk_name',
            'local_table',
            ['local_column_1', 'local_column_2'],
            'foreign_table',
            ['foreign_column_1', 'foreign_column_2'],
        ];
    }
}
