<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config\Definition;

use Smile\GdprDump\Config\Definition\DumpConfig;
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

        $config = (new DumpConfig());
        $config->setAddDropDatabase(true)
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

            $this->assertTrue($config->getAddDropDatabase());
            $this->assertFalse($config->getAddDropTable());
            $this->assertFalse($config->getAddDropTrigger());
            $this->assertFalse($config->getAddLocks());
            $this->assertTrue($config->getCompleteInsert());
            $this->assertSame($compress, $config->getCompress());
            $this->assertSame($defaultCharacterSet, $config->getDefaultCharacterSet());
            $this->assertFalse($config->getDisableKeys());
            $this->assertTrue($config->getEvents());
            $this->assertFalse($config->getExtendedInsert());
            $this->assertTrue($config->getHexBlob());
            $this->assertSame($initCommands, $config->getInitCommands());
            $this->assertTrue($config->getInsertIgnore());
            $this->assertTrue($config->getLockTables());
            $this->assertSame($netBufferLength, $config->getNetBufferLength());
            $this->assertTrue($config->getNoAutocommit());
            $this->assertTrue($config->getNoCreateInfo());
            $this->assertSame($output, $config->getOutput());
            $this->assertFalse($config->getSingleTransaction());
            $this->assertTrue($config->getSkipComments());
            $this->assertTrue($config->getSkipDefiner());
            $this->assertTrue($config->getSkipDumpDate());
            $this->assertTrue($config->getSkipTriggers());
            $this->assertTrue($config->getSkipTzUtc());
    }

    /**
     * Test the default values.
     */
    public function testDefaultValues(): void
    {
        $config = (new DumpConfig());
        $this->assertSame('php://stdout', $config->getOutput());
        $this->assertFalse($config->getAddDropDatabase());
        $this->assertTrue($config->getAddDropTable());
        $this->assertTrue($config->getAddDropTrigger());
        $this->assertTrue($config->getAddLocks());
        $this->assertFalse($config->getCompleteInsert());
        $this->assertSame('none', $config->getCompress());
        $this->assertSame('utf8', $config->getDefaultCharacterSet());
        $this->assertTrue($config->getDisableKeys());
        $this->assertFalse($config->getEvents());
        $this->assertTrue($config->getExtendedInsert());
        $this->assertFalse($config->getHexBlob());
        $this->assertSame([], $config->getInitCommands());
        $this->assertFalse($config->getInsertIgnore());
        $this->assertFalse($config->getLockTables());
        $this->assertSame(1000000, $config->getNetBufferLength());
        $this->assertTrue($config->getNoAutocommit());
        $this->assertFalse($config->getNoCreateInfo());
        $this->assertTrue($config->getSingleTransaction());
        $this->assertFalse($config->getSkipComments());
        $this->assertFalse($config->getSkipDefiner());
        $this->assertFalse($config->getSkipDumpDate());
        $this->assertFalse($config->getSkipTriggers());
        $this->assertFalse($config->getSkipTzUtc());
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
