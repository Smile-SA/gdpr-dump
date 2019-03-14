<?php
declare(strict_types=1);

namespace Smile\Anonymizer;

use Smile\Anonymizer\Command;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    const VERSION = '0.1.0';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('anonymizer', self::VERSION);

        $this->setDefaultCommand('anonymize', true);
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultCommands(): array
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Command\AnonymizeCommand();

        return $commands;
    }
}
