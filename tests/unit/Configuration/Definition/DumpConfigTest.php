<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Definition;

use Smile\GdprDump\Configuration\Definition\DumpConfig;
use Smile\GdprDump\Configuration\Exception\InvalidQueryException;
use Smile\GdprDump\Tests\Unit\TestCase;

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

        $dumpConfig = new DumpConfig();
        $dumpConfig->setAddDropDatabase(true)
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

            $this->assertTrue($dumpConfig->getAddDropDatabase());
            $this->assertFalse($dumpConfig->getAddDropTable());
            $this->assertFalse($dumpConfig->getAddDropTrigger());
            $this->assertFalse($dumpConfig->getAddLocks());
            $this->assertTrue($dumpConfig->getCompleteInsert());
            $this->assertSame($compress, $dumpConfig->getCompress());
            $this->assertSame($defaultCharacterSet, $dumpConfig->getDefaultCharacterSet());
            $this->assertFalse($dumpConfig->getDisableKeys());
            $this->assertTrue($dumpConfig->getEvents());
            $this->assertFalse($dumpConfig->getExtendedInsert());
            $this->assertTrue($dumpConfig->getHexBlob());
            $this->assertSame($initCommands, $dumpConfig->getInitCommands());
            $this->assertTrue($dumpConfig->getInsertIgnore());
            $this->assertTrue($dumpConfig->getLockTables());
            $this->assertSame($netBufferLength, $dumpConfig->getNetBufferLength());
            $this->assertTrue($dumpConfig->getNoAutocommit());
            $this->assertTrue($dumpConfig->getNoCreateInfo());
            $this->assertSame($output, $dumpConfig->getOutput());
            $this->assertFalse($dumpConfig->getSingleTransaction());
            $this->assertTrue($dumpConfig->getSkipComments());
            $this->assertTrue($dumpConfig->getSkipDefiner());
            $this->assertTrue($dumpConfig->getSkipDumpDate());
            $this->assertTrue($dumpConfig->getSkipTriggers());
            $this->assertTrue($dumpConfig->getSkipTzUtc());
    }

    /**
     * Test the default values.
     */
    public function testDefaultValues(): void
    {
        $dumpConfig = new DumpConfig();
        $this->assertSame('php://stdout', $dumpConfig->getOutput());
        $this->assertFalse($dumpConfig->getAddDropDatabase());
        $this->assertTrue($dumpConfig->getAddDropTable());
        $this->assertTrue($dumpConfig->getAddDropTrigger());
        $this->assertTrue($dumpConfig->getAddLocks());
        $this->assertFalse($dumpConfig->getCompleteInsert());
        $this->assertSame('none', $dumpConfig->getCompress());
        $this->assertSame('utf8', $dumpConfig->getDefaultCharacterSet());
        $this->assertTrue($dumpConfig->getDisableKeys());
        $this->assertFalse($dumpConfig->getEvents());
        $this->assertTrue($dumpConfig->getExtendedInsert());
        $this->assertFalse($dumpConfig->getHexBlob());
        $this->assertSame([], $dumpConfig->getInitCommands());
        $this->assertFalse($dumpConfig->getInsertIgnore());
        $this->assertFalse($dumpConfig->getLockTables());
        $this->assertSame(1000000, $dumpConfig->getNetBufferLength());
        $this->assertTrue($dumpConfig->getNoAutocommit());
        $this->assertFalse($dumpConfig->getNoCreateInfo());
        $this->assertTrue($dumpConfig->getSingleTransaction());
        $this->assertFalse($dumpConfig->getSkipComments());
        $this->assertFalse($dumpConfig->getSkipDefiner());
        $this->assertFalse($dumpConfig->getSkipDumpDate());
        $this->assertFalse($dumpConfig->getSkipTriggers());
        $this->assertFalse($dumpConfig->getSkipTzUtc());
    }

    /**
     * Assert that an exception is thrown when an init command contains a forbidden statement.
     */
    public function testInvalidStatementInInitCommand(): void
    {
        $this->expectException(InvalidQueryException::class);
        (new DumpConfig())->setInitCommands(['my_var' => 'select my_col from my_table; delete from my_table']);
    }
}
