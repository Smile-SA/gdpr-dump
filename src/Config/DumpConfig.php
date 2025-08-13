<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config;

use Smile\GdprDump\Config\Exception\MappingException;
use Smile\GdprDump\Config\Loader\Container;

final class DumpConfig extends Container
{
    public function getAddDropDatabase(): bool
    {
        return $this->get('add_drop_database', false);
    }

    public function setAddDropDatabase(bool $value): self
    {
        return $this->set('add_drop_database', $value);
    }

    public function getAddDropTable(): bool
    {
        return $this->get('add_drop_table', true);
    }

    public function setAddDropTable(bool $value): self
    {
        return $this->set('add_drop_table', $value);
    }

    public function getAddDropTrigger(): bool
    {
        return $this->get('add_drop_trigger', true);
    }

    public function setAddDropTrigger(bool $value): self
    {
        return $this->set('add_drop_trigger', $value);
    }

    public function getAddLocks(): bool
    {
        return $this->get('add_locks', true);
    }

    public function setAddLocks(bool $value): self
    {
        return $this->set('add_locks', $value);
    }

    public function getCompleteInsert(): bool
    {
        return $this->get('complete_insert', false);
    }

    public function setCompleteInsert(bool $value): self
    {
        return $this->set('complete_insert', $value);
    }

    public function getCompress(): string
    {
        return $this->get('compress', 'none');
    }

    public function setCompress(string $value): self
    {
        return $this->set('compress', $value);
    }

    public function getDefaultCharacterSet(): string
    {
        return $this->get('default_character_set', 'utf8');
    }

    public function setDefaultCharacterSet(string $value): self
    {
        return $this->set('default_character_set', $value);
    }

    public function getDisableKeys(): bool
    {
        return $this->get('disable_keys', true);
    }

    public function setDisableKeys(bool $value): self
    {
        return $this->set('disable_keys', $value);
    }

    public function getEvents(): bool
    {
        return $this->get('events', false);
    }

    public function setEvents(bool $value): self
    {
        return $this->set('events', $value);
    }

    public function getExtendedInsert(): bool
    {
        return $this->get('extended_insert', true);
    }

    public function setExtendedInsert(bool $value): self
    {
        return $this->set('extended_insert', $value);
    }

    public function getHexBlob(): bool
    {
        return $this->get('hex_blob', false);
    }

    public function setHexBlob(bool $value): self
    {
        return $this->set('hex_blob', $value);
    }

    public function getInitCommands(): array
    {
        return $this->get('init_commands', []);
    }

    public function setInitCommands(array $value): self
    {
        return $this->set('init_commands', $value);
    }

    public function getInsertIgnore(): bool
    {
        return $this->get('insert_ignore', false);
    }

    public function setInsertIgnore(bool $value): self
    {
        return $this->set('insert_ignore', $value);
    }

    public function getLockTables(): bool
    {
        return $this->get('lock_tables', false);
    }

    public function setLockTables(bool $value): self
    {
        return $this->set('lock_tables', $value);
    }

    public function getNetBufferLength(): int
    {
        return $this->get('net_buffer_length', 1000000);
    }

    public function setNetBufferLength(int $value): self
    {
        return $this->set('net_buffer_length', $value);
    }

    public function getNoAutocommit(): bool
    {
        return $this->get('no_autocommit', true);
    }

    public function setNoAutocommit(bool $value): self
    {
        return $this->set('no_autocommit', $value);
    }

    public function getNoCreateInfo(): bool
    {
        return $this->get('no_create_info', false);
    }

    public function setNoCreateInfo(bool $value): self
    {
        return $this->set('no_create_info', $value);
    }

    public function getRoutines(): bool
    {
        return $this->get('routines', false);
    }

    public function setRoutines(bool $value): self
    {
        return $this->set('routines', $value);
    }

    public function getSingleTransaction(): bool
    {
        return $this->get('single_transaction', true);
    }

    public function setSingleTransaction(bool $value): self
    {
        return $this->set('single_transaction', $value);
    }

    public function getSkipComments(): bool
    {
        return $this->get('skip_comments', false);
    }

    public function setSkipComments(bool $value): self
    {
        return $this->set('skip_comments', $value);
    }

    public function getSkipDefiner(): bool
    {
        return $this->get('skip_definer', false);
    }

    public function setSkipDefiner(bool $value): self
    {
        return $this->set('skip_definer', $value);
    }

    public function getSkipDumpDate(): bool
    {
        return $this->get('skip_dump_date', false);
    }

    public function setSkipDumpDate(bool $value): self
    {
        return $this->set('skip_dump_date', $value);
    }

    public function getSkipTriggers(): bool
    {
        return $this->get('skip_triggers', false);
    }

    public function setSkipTriggers(bool $value): self
    {
        return $this->set('skip_triggers', $value);
    }

    public function getSkipTzUtc(): bool
    {
        return $this->get('skip_tz_utc', false);
    }

    public function setSkipTzUtc(bool $value): self
    {
        return $this->set('skip_tz_utc', $value);
    }

    public function fromArray(array $items): self
    {
        foreach ($items as $property => $value) {
            $items[$property] = match ($property) {
                'add_drop_database' => $this->setAddDropDatabase($value),
                'add_drop_table' => $this->setAddDropTable($value),
                'add_drop_trigger' => $this->setAddDropTrigger($value),
                'add_locks' => $this->setAddLocks($value),
                'complete_insert' => $this->setCompleteInsert($value),
                'compress' => $this->setCompress($value),
                'default_character_set' => $this->setDefaultCharacterSet($value),
                'disable_keys' => $this->setDisableKeys($value),
                'events' => $this->setEvents($value),
                'extended_insert' => $this->setExtendedInsert($value),
                'hex_blob' => $this->setHexBlob($value),
                'init_commands' => $this->setInitCommands($value),
                'insert_ignore' => $this->setInsertIgnore($value),
                'lock_tables' => $this->setLockTables($value),
                'net_buffer_length' => $this->setNetBufferLength($value),
                'no_autocommit' => $this->setNoAutocommit($value),
                'no_create_info' => $this->setNoCreateInfo($value),
                'routines' => $this->setRoutines($value),
                'single_transaction' => $this->setSingleTransaction($value),
                'skip_comments' => $this->setSkipComments($value),
                'skip_definer' => $this->setSkipDefiner($value),
                'skip_dump_date' => $this->setSkipDumpDate($value),
                'skip_triggers' => $this->setSkipTriggers($value),
                'skip_tz_utc' => $this->setSkipTzUtc($value),
                default => throw new MappingException(sprintf('Unsupported dump property "%s".', $property)),
            };
        }

        return $this;
    }
}
