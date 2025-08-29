<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console\Helper;

use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Io
{
    private string $stdin = '';

    /**
     * Read from stdin.
     */
    public function readStdin(): string
    {
        if ($this->stdin !== '' || ftell(STDIN) > 0) {
            return $this->stdin;
        }

        if (!stream_isatty(STDIN)) {
            $this->stdin = stream_get_contents(STDIN);
        }

        return $this->stdin;
    }

    /**
     * Get the error output.
     */
    public function getStdErr(OutputInterface $output): OutputInterface
    {
        return $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
    }
}
