<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional\Database\Metadata;

use Smile\GdprDump\Database\Metadata\MetadataInterface;
use Smile\GdprDump\Database\Metadata\MysqlMetadata;
use Smile\GdprDump\Tests\Functional\DatabaseTestCase;

class MysqlMetadataTest extends DatabaseTestCase
{
    /**
     * Test the "getTableNames" method.
     */
    public function testTableNames()
    {
        $metadata = $this->getMetadata();

        // TODO: use assertEqualsCanonicalizing when upgrading to a newer version of PHPUnit
        // Currently we need to set $canonicalize argument to true to ignore the order of elements in the array
        $this->assertEquals(['stores', 'customers', 'addresses'], $metadata->getTableNames(), '', 0.0, 10, true);
    }

    /**
     * Test the "getForeignKeys" method.
     */
    public function testForeignKeys()
    {
        $metadata = $this->getMetadata();

        $this->validateStoresForeignKeys($metadata);
        $this->validateCustomersForeignKeys($metadata);
        $this->validateAddressesForeignKeys($metadata);
    }

    /**
     * Validate the foreign keys of the "stores" table.
     *
     * @param MetadataInterface $metadata
     */
    private function validateStoresForeignKeys(MetadataInterface $metadata)
    {
        $foreignKeys = $metadata->getForeignKeys('stores');
        $this->assertEquals([], $foreignKeys);
    }

    /**
     * Validate the foreign keys of the "customers" table.
     *
     * @param MetadataInterface $metadata
     */
    private function validateCustomersForeignKeys(MetadataInterface $metadata)
    {
        $foreignKeys = $metadata->getForeignKeys('customers');
        $this->assertCount(1, $foreignKeys);

        $foreignKey = reset($foreignKeys);
        $this->assertNotEmpty($foreignKey->getConstraintName());
        $this->assertSame('customers', $foreignKey->getLocalTableName());
        $this->assertSame(['store_id'], $foreignKey->getLocalColumns());
        $this->assertSame('stores', $foreignKey->getForeignTableName());
        $this->assertSame(['store_id'], $foreignKey->getForeignColumns());
    }

    /**
     * Validate the foreign keys of the "addresses" table.
     *
     * @param MetadataInterface $metadata
     */
    private function validateAddressesForeignKeys(MetadataInterface $metadata)
    {
        $foreignKeys = $metadata->getForeignKeys('addresses');
        $this->assertCount(1, $foreignKeys);

        $foreignKey = reset($foreignKeys);
        $this->assertNotEmpty($foreignKey->getConstraintName());
        $this->assertSame('addresses', $foreignKey->getLocalTableName());
        $this->assertSame(['customer_id'], $foreignKey->getLocalColumns());
        $this->assertSame('customers', $foreignKey->getForeignTableName());
        $this->assertSame(['customer_id'], $foreignKey->getForeignColumns());
    }

    /**
     * Get the metadata object.
     *
     * @return MysqlMetadata
     */
    private function getMetadata(): MysqlMetadata
    {
        $connection = $this->getDatabase()->getConnection();

        return new MysqlMetadata($connection);
    }
}
