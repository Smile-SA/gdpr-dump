<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console\Command;

use Exception;
use Smile\GdprDump\Config\ConfigException;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Config\Loader\ConfigLoaderInterface;
use Smile\GdprDump\Config\Validator\ValidationResultInterface;
use Smile\GdprDump\Config\Validator\ValidatorInterface;
use Smile\GdprDump\Dumper\DumperInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class DumpCommand extends Command
{
    public function __construct(
        private DumperInterface $dumper,
        private ConfigInterface $config,
        private ConfigLoaderInterface $configLoader,
        private ValidatorInterface $validator
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
            );
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            // Load the config
            $this->loadConfig($input);

            // Validate the config data
            $result = $this->validator->validate($this->config->toArray());
            if (!$result->isValid()) {
                $this->outputValidationResult($result, $output);
                return 1;
            }

            // Prompt for the password if not defined
            $database = $this->config->get('database', []);
            if (!array_key_exists('password', $database)) {
                $database['password'] = $this->promptPassword($input, $output);
                $this->config->set('database', $database);
            }

            $this->dumper->dump($this->config);
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
    private function loadConfig(InputInterface $input): void
    {
        // Load the config file(s)
        $configFiles = $input->getArgument('config_file');

        foreach ($configFiles as $configFile) {
            $this->configLoader->load($configFile);
        }

        $this->config->compile();
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
