<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console\Command;

use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Configuration\ConfigurationFactory;
use Smile\GdprDump\Configuration\Exception\JsonSchemaException;
use Smile\GdprDump\Console\Helper\DumpInfo;
use Smile\GdprDump\Console\Helper\Io;
use Smile\GdprDump\Dumper\DumperResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class DumpCommand extends Command
{
    public function __construct(
        private ConfigurationFactory $configurationFactory,
        private DumperResolver $dumperResolver,
        private EventDispatcherInterface $eventDispatcher,
        private Io $io,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        $this->setName('gdpr-dump')
            ->setDescription('Create an anonymized dump')
            ->addArgument('config_file', InputArgument::OPTIONAL, 'Dump configuration file in YAML format')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Database host')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Database port')
            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'Database user')
            // Default option is an empty array, this allows to differentiate between not using the option and using `--password`
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'Database password (prompts for one if the option is used without a value)', [])
            ->addOption('database', null, InputOption::VALUE_REQUIRED, 'Database name')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'The command will validate the configuration file, but won\'t actually perform the dump');
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $configuration = $this->loadConfig($input, $output);

            if ($output->isVerbose()) {
                $dumpInfo = new DumpInfo($this->io->getStdErr($output), $this->eventDispatcher);
                $dumpInfo->addListeners();
            }

            $this->dumperResolver
                ->getDumper($configuration)
                ->dump($configuration, $input->getOption('dry-run'));
        } catch (Throwable $e) {
            $this->handleException($e, $output);
            return Command::FAILURE;
        } finally {
            isset($dumpInfo) && $dumpInfo->removeListeners();
        }

        return Command::SUCCESS;
    }

    /**
     * Load the dump config.
     */
    private function loadConfig(InputInterface $input, OutputInterface $output): Configuration
    {
        $builder = $this->configurationFactory->createBuilder();

        $fileName = (string) $input->getArgument('config_file');

        if ($fileName !== '' && $fileName !== '-') {
            // Read the provided configuration file
            $configuration = $builder->build($fileName, true);
        } else {
            // Read from stdin if it is not empty
            $stdin = $this->io->readStdin();
            if ($stdin !== '') {
                $configuration = $builder->build($stdin, false);
            } else {
                $configuration = $builder->build();
            }
        }

        // Add command-line options to the connection params (e.g. `--database`)
        $this->addInputOptionsToConnectionParams($configuration, $input, $output);

        // Exit if connection params are empty
        if (!$configuration->getConnectionParams()) {
            $message = <<<'EOT'
                Please define the connection settings in a configuration file,
                or with command-line options such as `--database`.
                EOT;
            throw new InvalidOptionException($message);
        }

        return $configuration;
    }

    /**
     * Update database config from command-line options.
     */
    private function addInputOptionsToConnectionParams(
        Configuration $configuration,
        InputInterface $input,
        OutputInterface $output,
    ): void {
        $connectionParams = $configuration->getConnectionParams();

        // Process host, port, user, database
        foreach (['host', 'port', 'user', 'database'] as $option) {
            $value = $input->getOption($option);
            if ($value === null) {
                continue; // Option was not provided
            }

            if ($value === '') {
                throw new InvalidOptionException(sprintf('The "--%s" option requires a non-empty value.', $option));
            }

            $param = $option === 'database' ? 'dbname' : $option;
            $connectionParams[$param] = $value;
        }

        // Process the password (prompt for one if `--password` was passed without a value)
        $password = $input->getOption('password');
        if ($password !== []) {
            $connectionParams['password'] = $password ?? $this->promptPassword($input, $output);
        }

        $configuration->setConnectionParams($connectionParams);
    }

    /**
     * Display a password prompt, and return the user input.
     */
    private function promptPassword(InputInterface $input, OutputInterface $output): string
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new Question('Enter database password: ', '');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        return trim($helper->ask($input, $output, $question));
    }

    /**
     * Display the provided exception.
     */
    private function handleException(Throwable $exception, OutputInterface $output): void
    {
        $stdErr = $this->io->getStdErr($output);

        if ($exception instanceof JsonSchemaException) {
            // Display errors detected by the JSON schema validator
            $stdErr->writeln('<error>The configuration is invalid:</error>');
            foreach ($exception->getMessages() as $message) {
                $stdErr->writeln('  - ' . $message);
            }
            return;
        }

        if ($output->isDebug() || $exception instanceof InvalidOptionException) {
            throw $exception;
        }

        $stdErr->writeln($exception->getMessage());
    }
}
