<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console\Command;

use Exception;
use Smile\GdprDump\Config\Compiler\CompilerInterface;
use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\ConfigException;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Config\Loader\ConfigLoaderInterface;
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

class DumpCommand extends Command
{
    public function __construct(
        private DumperInterface $dumper,
        private ConfigLoaderInterface $configLoader,
        private ValidatorInterface $validator,
        private CompilerInterface $compiler,
        private DumpInfo $dumpInfo
    ) {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function configure(): void
    {
        $this->setName('gdpr-dump')
            ->setDescription('Create an anonymized dump')
            ->addArgument(
                'config_file',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Dump configuration file(s)'
            )
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Database host')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Database port')
            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'Database user')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Database password')
            ->addOption('database', null, InputOption::VALUE_REQUIRED, 'Database name');
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            // Load the config
            $config = $this->loadConfig($input);

            // Validate the config data
            $result = $this->validator->validate($config->toArray());
            if (!$result->isValid()) {
                $this->outputValidationResult($result, $output);
                return 1;
            }

            // Prompt for the password if not defined
            $database = $config->get('database', []);
            if (!array_key_exists('password', $database)) {
                $database['password'] = $this->promptPassword($input, $output);
                $config->set('database', $database);
            }

            if ($output->isVerbose()) {
                $this->dumpInfo->setOutput($output);
            }

            $this->dumper->dump($config);
        } catch (Exception $e) {
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
     * @throws ConfigException
     */
    private function addInputOptionsToConfig(ConfigInterface $config, InputInterface $input): void
    {
        $databaseConfig = $config->get('database', []);

        foreach (['host', 'port', 'user', 'password', 'database'] as $option) {
            $value = $input->getOption($option);
            if ($value === null) {
                // Option was not provided
                continue;
            }

            if ($value === '' && $option !== 'password') {
                // Option must have a value (except the "password" option)
                throw new ConfigException(sprintf('Please provide a value for the option "%s".', $option));
            }

            $configKey = $option === 'database' ? 'name' : $option;
            if ($value === '') {
                // Remove the password from the config if an empty value was provided
                unset($databaseConfig[$configKey]);
                continue;
            }

            // Override the config value with the provided option value
            $databaseConfig[$configKey] = $value;
        }

        if (!empty($databaseConfig)) {
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
