<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console\Command;

use Smile\GdprDump\Config\DumperConfig;
use Smile\GdprDump\Config\Exception\JsonSchemaValidationException;
use Smile\GdprDump\Config\Loader\Loader;
use Smile\GdprDump\Config\Mapper\ObjectMapper;
use Smile\GdprDump\Config\Resource\FileResource;
use Smile\GdprDump\Config\Resource\JsonResource;
use Smile\GdprDump\Console\Helper\DumpInfo;
use Smile\GdprDump\Database\Driver\DatabaseDriver;
use Smile\GdprDump\Dumper\DumperFactory;
use Smile\GdprDump\Util\Objects;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;
use UnexpectedValueException;

final class DumpCommand extends Command
{
    public function __construct(
        private DumperFactory $dumperFactory,
        private Loader $configLoader,
        private ObjectMapper $mapper,
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
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
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
            $config = $this->loadConfig($input);

            $dumperConfig = new DumperConfig();
            $dumperConfig->fromArray(Objects::toArray($config));

            // Prompt for the password if not defined
            $connectionParams = $dumperConfig->getConnectionParams();
            if (!array_key_exists('password', $connectionParams)) { // TODO also check empty string?
                $connectionParams['password'] = $this->promptPassword($input, $output);
                $dumperConfig->setConnectionParams($connectionParams);
            }

            if ($output->isVerbose()) {
                $dumpInfo = new DumpInfo($output);
                $dumpInfo->addListeners($this->eventDispatcher);
            }

            $this->dumperFactory->create($dumperConfig)
                ->dump($dumperConfig, $input->getOption('dry-run'));
        } catch (JsonSchemaValidationException $e) {
            $this->outputValidationResult($e->getMessages(), $output);

            return Command::FAILURE;
        } catch (Throwable $e) {
            if ($output->isVerbose()) {
                throw $e;
            }

            $this->getErrorOutput($output)->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } finally {
            isset($dumpInfo) && $dumpInfo->removeListeners($this->eventDispatcher);
        }

        return Command::SUCCESS;
    }

    /**
     * Load the dump config.
     *
     * @throws ConfigException
     */
    private function loadConfig(InputInterface $input): object
    {
        // Load the provided files
        $resources = (array) $input->getArgument('config_file');
        foreach ($resources as $resource) {
            $this->configLoader->addResource(new FileResource($resource));
        }

        // Add the database configuration that was specified in the command-line options
        $databaseConfig = $this->getDatabaseConfigFromInput($input);
        if ($databaseConfig) {
            $this->configLoader->addResource(new JsonResource(json_encode(['database' => $databaseConfig])));
        }

        return $this->configLoader->load();
    }

    /**
     * Get database config from input options (e.g. `--database`).
     */
    private function getDatabaseConfigFromInput(InputInterface $input): array
    {
        $databaseConfig = [];

        foreach (['host', 'port', 'user', 'password', 'database'] as $option) {
            $value = $input->getOption($option);
            if ($value === null) {
                continue; // Option was not provided
            }

            if ($value === '' && $option !== 'password') {
                // Option must have a value (except password)
                throw new UnexpectedValueException(sprintf('Please provide a value for the option "%s".', $option));
            }

            // Override the config value with the provided option value
            $param = $option === 'database' ? 'name' : $option;
            $databaseConfig[$param] = $value;
        }

        return $databaseConfig;
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
     * Display the validation result.
     *
     * @param string[] $messages
     */
    private function outputValidationResult(array $messages, OutputInterface $output): void
    {
        $stdErr = $this->getErrorOutput($output);
        $stdErr->writeln('<error>The following errors were detected:</error>');
        foreach ($messages as $message) {
            $stdErr->writeln('  - ' . $message);
        }
    }

    /**
     * Get the error output.
     */
    private function getErrorOutput(OutputInterface $output): OutputInterface
    {
        return $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
    }
}
