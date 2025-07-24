<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console\Command;

use Smile\GdprDump\Config\Compiler\CompilerInterface;
use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\ConfigException;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Config\Loader\ConfigLoaderInterface;
use Smile\GdprDump\Config\Validator\ValidationException;
use Smile\GdprDump\Config\Validator\ValidationResultInterface;
use Smile\GdprDump\Config\Validator\ValidatorInterface;
use Smile\GdprDump\Console\Helper\DumpInfo;
use Smile\GdprDump\Dumper\DumperInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Throwable;

final class DumpCommand extends Command
{
    public function __construct(
        private DumperInterface $dumper,
        private ConfigLoaderInterface $configLoader,
        private ValidatorInterface $validator,
        private CompilerInterface $compiler,
        private DumpInfo $dumpInfo,
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

            // Validate the config data
            $result = $this->validator->validate($config->toArray());
            if (!$result->isValid()) {
                $this->outputValidationResult($result, $output);
                return 1;
            }

            // Prompt for the password if not defined
            $database = (array) $config->get('database', []);
            if (!array_key_exists('password', $database)) {
                $database['password'] = $this->promptPassword($input, $output);
                $config->set('database', $database);
            }

            if ($output->isVerbose()) {
                $this->dumpInfo->setOutput($output);
            }

            $this->dumper->dump($config, $input->getOption('dry-run'));
        } catch (Throwable $e) {
            if ($output->isVerbose()) {
                throw $e;
            }

            $this->getErrorOutput($output)->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }

        return 0;
    }

    /**
     * Load the dump config.
     *
     * @throws ConfigException
     */
    private function loadConfig(InputInterface $input): ConfigInterface
    {
        $config = new Config();

        // Load config files
        foreach ($input->getArgument('config_file') as $configFile) {
            $this->configLoader->load($configFile, $config);
        }

        // Add database config from input options
        $this->addInputOptionsToConfig($config, $input);

        // Compile the config
        $this->compiler->compile($config);

        return $config;
    }

    /**
     * Add input option values to the config.
     *
     * @throws ValidationException
     */
    private function addInputOptionsToConfig(ConfigInterface $config, InputInterface $input): void
    {
        $databaseConfig = (array) $config->get('database', []);

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
                throw new ValidationException(sprintf('Please provide a value for the option "%s".', $option));
            }

            // Override the config value with the provided option value
            $configKey = $option === 'database' ? 'name' : $option;
            $databaseConfig[$configKey] = $value;
        }

        if ($databaseConfig) {
            $config->set('database', $databaseConfig);
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
     */
    private function outputValidationResult(ValidationResultInterface $result, OutputInterface $output): void
    {
        $stdErr = $this->getErrorOutput($output);
        $stdErr->writeln('<error>The following errors were detected:</error>');
        foreach ($result->getMessages() as $message) {
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
