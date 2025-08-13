<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Definition;

use Smile\GdprDump\Configuration\Validator\QueryValidator;

final class DumpConfig
{
    private string $output = 'php://stdout';

    // druidfi/mysqldump-php settings
    private bool $addDropDatabase = false;
    private bool $addDropTable = true;
    private bool $addDropTrigger = true; // false in MySQLDump-PHP
    private bool $addLocks = true;
    private bool $completeInsert = false;
    private string $compress = 'none';
    private string $defaultCharacterSet = 'utf8';
    private bool $disableKeys = true;
    private bool $events = false;
    private bool $extendedInsert = true;
    private bool $hexBlob = false; // true in MySQLDump-PHP
    private array $initCommands = [];
    private bool $insertIgnore = false;
    private bool $lockTables = false; // true in MySQLDump-PHP
    private int $netBufferLength = 1000000;
    private bool $noAutocommit = true;
    private bool $noCreateInfo = false;
    private bool $routines = false;
    private bool $singleTransaction = true;
    private bool $skipComments = false;
    private bool $skipDefiner = false;
    private bool $skipDumpDate = false;
    private bool $skipTriggers = false;
    private bool $skipTzUtc = false;

    public function getOutput(): string
    {
        return $this->output;
    }

    public function setOutput(string $output): self
    {
        $this->output = $output;

        return $this;
    }

    public function getAddDropDatabase(): bool
    {
        return $this->addDropDatabase;
    }

    public function setAddDropDatabase(bool $addDropDatabase): self
    {
        $this->addDropDatabase = $addDropDatabase;

        return $this;
    }

    public function getAddDropTable(): bool
    {
        return $this->addDropTable;
    }

    public function setAddDropTable(bool $addDropTable): self
    {
        $this->addDropTable = $addDropTable;

        return $this;
    }

    public function getAddDropTrigger(): bool
    {
        return $this->addDropTrigger;
    }

    public function setAddDropTrigger(bool $addDropTrigger): self
    {
        $this->addDropTrigger = $addDropTrigger;

        return $this;
    }

    public function getAddLocks(): bool
    {
        return $this->addLocks;
    }

    public function setAddLocks(bool $addLocks): self
    {
        $this->addLocks = $addLocks;

        return $this;
    }

    public function getCompleteInsert(): bool
    {
        return $this->completeInsert;
    }

    public function setCompleteInsert(bool $completeInsert): self
    {
        $this->completeInsert = $completeInsert;

        return $this;
    }

    public function getCompress(): string
    {
        return $this->compress;
    }

    public function setCompress(string $compress): self
    {
        $this->compress = $compress;

        return $this;
    }

    public function getDefaultCharacterSet(): string
    {
        return $this->defaultCharacterSet;
    }

    public function setDefaultCharacterSet(string $defaultCharacterSet): self
    {
        $this->defaultCharacterSet = $defaultCharacterSet;

        return $this;
    }

    public function getDisableKeys(): bool
    {
        return $this->disableKeys;
    }

    public function setDisableKeys(bool $disableKeys): self
    {
        $this->disableKeys = $disableKeys;

        return $this;
    }

    public function getEvents(): bool
    {
        return $this->events;
    }

    public function setEvents(bool $events): self
    {
        $this->events = $events;

        return $this;
    }

    public function getExtendedInsert(): bool
    {
        return $this->extendedInsert;
    }

    public function setExtendedInsert(bool $extendedInsert): self
    {
        $this->extendedInsert = $extendedInsert;

        return $this;
    }

    public function getHexBlob(): bool
    {
        return $this->hexBlob;
    }

    public function setHexBlob(bool $hexBlob): self
    {
        $this->hexBlob = $hexBlob;

        return $this;
    }

    public function getInitCommands(): array
    {
        return $this->initCommands;
    }

    public function setInitCommands(array $initCommands): self
    {
        // Validate SQL queries
        $queryValidator = new QueryValidator(['set']);
        array_walk($initCommands, fn (string $query) => $queryValidator->validate($query));
        $this->initCommands = $initCommands;

        return $this;
    }

    public function getInsertIgnore(): bool
    {
        return $this->insertIgnore;
    }

    public function setInsertIgnore(bool $insertIgnore): self
    {
        $this->insertIgnore = $insertIgnore;

        return $this;
    }

    public function getLockTables(): bool
    {
        return $this->lockTables;
    }

    public function setLockTables(bool $lockTables): self
    {
        $this->lockTables = $lockTables;

        return $this;
    }

    public function getNetBufferLength(): int
    {
        return $this->netBufferLength;
    }

    public function setNetBufferLength(int $netBufferLength): self
    {
        $this->netBufferLength = $netBufferLength;

        return $this;
    }

    public function getNoAutocommit(): bool
    {
        return $this->noAutocommit;
    }

    public function setNoAutocommit(bool $noAutocommit): self
    {
        $this->noAutocommit = $noAutocommit;

        return $this;
    }

    public function getNoCreateInfo(): bool
    {
        return $this->noCreateInfo;
    }

    public function setNoCreateInfo(bool $noCreateInfo): self
    {
        $this->noCreateInfo = $noCreateInfo;

        return $this;
    }

    public function getRoutines(): bool
    {
        return $this->routines;
    }

    public function setRoutines(bool $routines): self
    {
        $this->routines = $routines;

        return $this;
    }

    public function getSingleTransaction(): bool
    {
        return $this->singleTransaction;
    }

    public function setSingleTransaction(bool $singleTransaction): self
    {
        $this->singleTransaction = $singleTransaction;

        return $this;
    }

    public function getSkipComments(): bool
    {
        return $this->skipComments;
    }

    public function setSkipComments(bool $skipComments): self
    {
        $this->skipComments = $skipComments;

        return $this;
    }

    public function getSkipDefiner(): bool
    {
        return $this->skipDefiner;
    }

    public function setSkipDefiner(bool $skipDefiner): self
    {
        $this->skipDefiner = $skipDefiner;

        return $this;
    }

    public function getSkipDumpDate(): bool
    {
        return $this->skipDumpDate;
    }

    public function setSkipDumpDate(bool $skipDumpDate): self
    {
        $this->skipDumpDate = $skipDumpDate;

        return $this;
    }

    public function getSkipTriggers(): bool
    {
        return $this->skipTriggers;
    }

    public function setSkipTriggers(bool $skipTriggers): self
    {
        $this->skipTriggers = $skipTriggers;

        return $this;
    }

    public function getSkipTzUtc(): bool
    {
        return $this->skipTzUtc;
    }

    public function setSkipTzUtc(bool $skipTzUtc): self
    {
        $this->skipTzUtc = $skipTzUtc;

        return $this;
    }
}
