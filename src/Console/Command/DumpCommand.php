<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console\Command;

use Smile\GdprDump\Configuration\ConfigurationFactory;
use Smile\GdprDump\Console\Helper\DumpInfo;
use Smile\GdprDump\Dumper\DumperFactory;
use Smile\GdprDump\Exception\JsonSchemaException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class DumpCommand extends Command
{
    public function __construct(
        private ConfigurationFactory $configurationFactory,
        private DumperFactory $dumperFactory,
        private EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $configHint = ' (can also be specified in the configuration file)';

        // phpcs:disable Generic.Files.LineLength.TooLong
        $this->setName('gdpr-dump')
            ->setDescription('Create an anonymized dump')
            ->addArgument(
                'config_file',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'Dump configuration file(s)'
            )
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Database host' . $configHint)
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Database port' . $configHint)
            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'Database user' . $configHint)
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Database password' . $configHint)
            ->addOption('database', null, InputOption::VALUE_REQUIRED, 'Database name' . $configHint)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'The command will validate the configuration file, but won\'t actually perform the dump');
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            // Load the config file(s)
            $configuration = $this->loadConfig($input, $output);

            if ($output->isVerbose()) {
                $dumpInfo = new DumpInfo($output, $this->eventDispatcher);
                $dumpInfo->addListeners();
            }

            $this->dumperFactory
                ->create($configuration)
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
    private function loadConfig(InputInterface $input, OutputInterface $output): object
    {
        $builder = $this->configurationFactory->createBuilder();

        // Load the provided files
        $fileNames = (array) $input->getArgument('config_file');
        foreach ($fileNames as $fileName) {
            $builder->addResource($this->configurationFactory->createFileResource($fileName));
        }

        // Add input from stdin
        $stdin = $this->getStdin();
        if ($stdin !== '') {
            $builder->addResource($this->configurationFactory->createJsonResource($stdin));
        }

        $configuration = $builder->build();
        $connectionParams = $configuration->getConnectionParams();

        // Add command-line options to the connection params (e.g. `--database`)
        $this->addInputOptionsToConnectionParams($connectionParams, $input);

        // Prompt for the password if not defined
        if ($connectionParams && !array_key_exists('password', $connectionParams)) {
            $connectionParams['password'] = $this->promptPassword($input, $output);
        }

        $configuration->setConnectionParams($connectionParams);

        return $configuration;
    }

    /**
     * Update database config from command-line options.
     */
    private function addInputOptionsToConnectionParams(array &$connectionParams, InputInterface $input): void
    {
        foreach (['host', 'port', 'user', 'password', 'database'] as $option) {
            $value = $input->getOption($option);
            if ($value === null) {
                continue; // Option was not provided
            }

            if ($value === '' && $option !== 'password') {
                // Disallow empty string (except for password)
                throw new InvalidOptionException(sprintf('The "--%s" option requires a non-empty value.', $option));
            }

            // Override the value
            $param = $option === 'database' ? 'dbname' : $option;
            $connectionParams[$param] = $value;
        }
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
        if ($exception instanceof JsonSchemaException) {
            // Display errors detected by the JSON schema validator
            $stdErr = $this->getErrorOutput($output);
            $stdErr->writeln('<error>The configuration is invalid:</error>');
            foreach ($exception->getMessages() as $message) {
                $stdErr->writeln('  - ' . $message);
            }
            return;
        }

        if ($output->isDebug() || $exception instanceof InvalidOptionException) {
            throw $exception;
        }

        $this->getErrorOutput($output)->writeln($exception->getMessage());
    }

    /**
     * Get the value passed in stdin (if any).
     */
    private function getStdin(): string
    {
        $stdin = '';
        $fh = fopen('php://stdin', 'r');
        stream_set_blocking($fh, false);

        while ($line = fgets($fh)) {
            $stdin .= $line;
        }

        return $stdin;
    }

    /**
     * Get the error output.
     */
    private function getErrorOutput(OutputInterface $output): OutputInterface
    {
        return $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
    }
}
