<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional\Database\Metadata;

use RuntimeException;
use Smile\GdprDump\Database\Metadata\MetadataInterface;
use Smile\GdprDump\Database\Metadata\MysqlMetadata;
use Smile\GdprDump\Tests\Functional\TestCase;

class MysqlMetadataTest extends TestCase
{
    /**
     * Test the "getTableNames" method.
     */
    public function testTableNames(): void
    {
        $metadata = $this->getMetadata();
        $this->assertEqualsCanonicalizing(['stores', 'customers', 'addresses', 'config'], $metadata->getTableNames());
    }

    /**
     * Test the "getColumnNames" method.
     */
    public function testColumnNames(): void
    {
        $columns = $this->getMetadata()->getColumnNames('stores');
        $this->assertEqualsCanonicalizing(['code', 'store_id', 'parent_store_id'], $columns);
    }

    /**
     * Assert that an exception is thrown when fetching the columns of an undefined table.
     */
    public function testErrorOnInvalidTableName(): void
    {
        $this->expectException(RuntimeException::class);
        $this->getMetadata()->getColumnNames('undefined');
    }

    /**
     * Test the "getForeignKeys" method.
     */
    public function testForeignKeys(): void
    {
        $metadata = $this->getMetadata();

        $this->validateStoresForeignKeys($metadata);
        $this->validateCustomersForeignKeys($metadata);
        $this->validateAddressesForeignKeys($metadata);
    }

    /**
     * Validate the foreign keys of the "stores" table.
     */
    private function validateStoresForeignKeys(MetadataInterface $metadata): void
    {
        $foreignKeys = $metadata->getTableForeignKeys('stores');
        $this->assertCount(1, $foreignKeys);

        $foreignKey = reset($foreignKeys);
        $this->assertNotEmpty($foreignKey->getConstraintName());
        $this->assertSame('stores', $foreignKey->getLocalTableName());
        $this->assertSame(['parent_store_id'], $foreignKey->getLocalColumns());
        $this->assertSame('stores', $foreignKey->getForeignTableName());
        $this->assertSame(['store_id'], $foreignKey->getForeignColumns());
    }

    /**
     * Validate the foreign keys of the "customers" table.
     */
    private function validateCustomersForeignKeys(MetadataInterface $metadata): void
    {
        $foreignKeys = $metadata->getTableForeignKeys('customers');
        $this->assertCount(3, $foreignKeys);

        $foreignKey = reset($foreignKeys);
        $this->assertNotEmpty($foreignKey->getConstraintName());
        $this->assertSame('customers', $foreignKey->getLocalTableName());
        $this->assertSame(['billing_address_id'], $foreignKey->getLocalColumns());
        $this->assertSame('addresses', $foreignKey->getForeignTableName());
        $this->assertSame(['address_id'], $foreignKey->getForeignColumns());

        $foreignKey = next($foreignKeys);
        $this->assertNotEmpty($foreignKey->getConstraintName());
        $this->assertSame('customers', $foreignKey->getLocalTableName());
        $this->assertSame(['shipping_address_id'], $foreignKey->getLocalColumns());
        $this->assertSame('addresses', $foreignKey->getForeignTableName());
        $this->assertSame(['address_id'], $foreignKey->getForeignColumns());

        $foreignKey = next($foreignKeys);
        $this->assertNotEmpty($foreignKey->getConstraintName());
        $this->assertSame('customers', $foreignKey->getLocalTableName());
        $this->assertSame(['store_id'], $foreignKey->getLocalColumns());
        $this->assertSame('stores', $foreignKey->getForeignTableName());
        $this->assertSame(['store_id'], $foreignKey->getForeignColumns());
    }

    /**
     * Validate the foreign keys of the "addresses" table.
     */
    private function validateAddressesForeignKeys(MetadataInterface $metadata): void
    {
        $foreignKeys = $metadata->getTableForeignKeys('addresses');
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
     */
    private function getMetadata(): MysqlMetadata
    {
        $connection = $this->getDatabase()->getConnection();

        return new MysqlMetadata($connection);
    }
}
