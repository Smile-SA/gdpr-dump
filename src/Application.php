<?php
declare(strict_types=1);

namespace Smile\Anonymizer;

use Smile\Anonymizer\Command;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('anonymizer', '1.0.0');

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
