<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Definition;

use Smile\GdprDump\Configuration\Definition\DumpConfig;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

final class DumpConfigTest extends TestCase
{
    /**
     * Test the creation of a dump config object.
     */
    public function testObjectCreation(): void
    {
        $output = 'dump.sql';
        $compress = 'gzip';
        $defaultCharacterSet = 'utf8mb4';
        $initCommands = ['SET foo'];
        $netBufferLength = 10000;

        $configuration = (new DumpConfig());
        $configuration->setAddDropDatabase(true)
            ->setAddDropTable(false)
            ->setAddDropTrigger(false)
            ->setAddLocks(false)
            ->setCompleteInsert(true)
            ->setCompress($compress)
            ->setDefaultCharacterSet($defaultCharacterSet)
            ->setDisableKeys(false)
            ->setEvents(true)
            ->setExtendedInsert(false)
            ->setHexBlob(true)
            ->setInitCommands($initCommands)
            ->setInsertIgnore(true)
            ->setLockTables(true)
            ->setNetBufferLength($netBufferLength)
            ->setNoAutocommit(true)
            ->setNoCreateInfo(true)
            ->setOutput($output)
            ->setSingleTransaction(false)
            ->setSkipComments(true)
            ->setSkipDefiner(true)
            ->setSkipDumpDate(true)
            ->setSkipTriggers(true)
            ->setSkipTzUtc(true);

            $this->assertTrue($configuration->getAddDropDatabase());
            $this->assertFalse($configuration->getAddDropTable());
            $this->assertFalse($configuration->getAddDropTrigger());
            $this->assertFalse($configuration->getAddLocks());
            $this->assertTrue($configuration->getCompleteInsert());
            $this->assertSame($compress, $configuration->getCompress());
            $this->assertSame($defaultCharacterSet, $configuration->getDefaultCharacterSet());
            $this->assertFalse($configuration->getDisableKeys());
            $this->assertTrue($configuration->getEvents());
            $this->assertFalse($configuration->getExtendedInsert());
            $this->assertTrue($configuration->getHexBlob());
            $this->assertSame($initCommands, $configuration->getInitCommands());
            $this->assertTrue($configuration->getInsertIgnore());
            $this->assertTrue($configuration->getLockTables());
            $this->assertSame($netBufferLength, $configuration->getNetBufferLength());
            $this->assertTrue($configuration->getNoAutocommit());
            $this->assertTrue($configuration->getNoCreateInfo());
            $this->assertSame($output, $configuration->getOutput());
            $this->assertFalse($configuration->getSingleTransaction());
            $this->assertTrue($configuration->getSkipComments());
            $this->assertTrue($configuration->getSkipDefiner());
            $this->assertTrue($configuration->getSkipDumpDate());
            $this->assertTrue($configuration->getSkipTriggers());
            $this->assertTrue($configuration->getSkipTzUtc());
    }

    /**
     * Test the default values.
     */
    public function testDefaultValues(): void
    {
        $configuration = (new DumpConfig());
        $this->assertSame('php://stdout', $configuration->getOutput());
        $this->assertFalse($configuration->getAddDropDatabase());
        $this->assertTrue($configuration->getAddDropTable());
        $this->assertTrue($configuration->getAddDropTrigger());
        $this->assertTrue($configuration->getAddLocks());
        $this->assertFalse($configuration->getCompleteInsert());
        $this->assertSame('none', $configuration->getCompress());
        $this->assertSame('utf8', $configuration->getDefaultCharacterSet());
        $this->assertTrue($configuration->getDisableKeys());
        $this->assertFalse($configuration->getEvents());
        $this->assertTrue($configuration->getExtendedInsert());
        $this->assertFalse($configuration->getHexBlob());
        $this->assertSame([], $configuration->getInitCommands());
        $this->assertFalse($configuration->getInsertIgnore());
        $this->assertFalse($configuration->getLockTables());
        $this->assertSame(1000000, $configuration->getNetBufferLength());
        $this->assertTrue($configuration->getNoAutocommit());
        $this->assertFalse($configuration->getNoCreateInfo());
        $this->assertTrue($configuration->getSingleTransaction());
        $this->assertFalse($configuration->getSkipComments());
        $this->assertFalse($configuration->getSkipDefiner());
        $this->assertFalse($configuration->getSkipDumpDate());
        $this->assertFalse($configuration->getSkipTriggers());
        $this->assertFalse($configuration->getSkipTzUtc());
    }

    /**
     * Assert that an exception is thrown when an init command contains a forbidden statement.
     */
    public function testInvalidStatementInInitCommand(): void
    {
        $this->expectException(UnexpectedValueException::class);
        (new DumpConfig())->setInitCommands(['my_var' => 'select my_col from my_table; delete from my_table']);
    }
}
