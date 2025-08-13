<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console\Command;

use Smile\GdprDump\Config\DumperConfig;
use Smile\GdprDump\Config\Exception\JsonSchemaValidationException;
use Smile\GdprDump\Config\Loader\ConfigLoader;
use Smile\GdprDump\Config\Mapper\ObjectMapper;
use Smile\GdprDump\Config\Parser\Enum\Format;
use Smile\GdprDump\Config\Parser\Enum\Formats;
use Smile\GdprDump\Console\Helper\DumpInfo;
use Smile\GdprDump\Dumper\Dumper;
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
        private Dumper $dumper,
        private ConfigLoader $configLoader,
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
            ->addOption('json', null, InputOption::VALUE_NONE, 'Allows to specify a JSON string instead of a YAML file')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'The command will validate the configuration file, but won\'t actually perform the dump');
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            // Load the config file(s)
            $config = $this->loadConfig($input);

            // TODO REMOVE
            // Validate the config data
            /*$result = $this->validator->validate($config);
            if (!$result->isValid()) {
                $this->outputValidationResult($result, $output);
                return Command::FAILURE;
            }*/

            // Prompt for the password if not defined
            if (!property_exists($config->database, 'password')) {
                $config->database->password = $this->promptPassword($input, $output);
            }

            if ($output->isVerbose()) {
                $dumpInfo = new DumpInfo($output);
                $dumpInfo->addListeners($this->eventDispatcher);
            }

            $this->dumper->dump($config, $input->getOption('dry-run'));
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
        $config = new DumperConfig();

        // Read YAML files, or json strings if `--json` option was provided
        $resources = (array) $input->getArgument('config_file');
        $format = $input->getOption('json')
            ? Formats::JSON
            : Formats::YAML_FILE;

        foreach ($resources as $resource) {
            $this->configLoader->addResource($resource, $format);
        }

        $object = $this->configLoader
            ->addResource('{"faker":{"locale": "it_IT"}, "version": "3.0.0"}', Formats::JSON)
            ->load();

        $this->dumper->dump2($this->configLoader, true);
        exit;

            //->mapToConfig($config);
            //
            var_dump($object);
            exit;

            $config->freeze();
        var_dump($config->toArray());
        $config->fromArray([]);
        exit;
        //$config = $this->mapper->build($parsed);
        // TODO

        // Add database config from input options
        $this->addInputOptionsToConfig($config, $input);

        return $config;
    }

    /**
     * Add input option values to the config.
     *
     * @throws ValidationException
     */
    private function addInputOptionsToConfig(object $config, InputInterface $input): void
    {
        $database = $config->database;

        foreach (['host', 'port', 'user', 'password', 'database'] as $option) {
            $value = $input->getOption($option);
            if ($value === null) {
                // Option was not provided
                continue;
            }

            if ($value === '') {
                if ($option === 'password') {
                    // Remove the password from the config if an empty value was provided
                    unset($databaseConfig['password']);
                    continue;
                }

                // Option must have a value
                throw new UnexpectedValueException(sprintf('Please provide a value for the option "%s".', $option));
            }

            // Override the config value with the provided option value
            $configKey = $option === 'database' ? 'name' : $option;
            $database->{$configKey} = $value;
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
