<?php
declare(strict_types=1);

namespace Smile\GdprDump\Console\Command;

use Exception;
use RuntimeException;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Config\ConfigLoaderInterface;
use Smile\GdprDump\Config\Validator\ValidationResultInterface;
use Smile\GdprDump\Config\Validator\ValidatorInterface;
use Smile\GdprDump\Dumper\DumperInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @codeCoverageIgnore
 */
class DumpCommand extends Command
{
    /**
     * @var DumperInterface
     */
    private $dumper;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ConfigLoaderInterface
     */
    private $configLoader;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param DumperInterface $dumper
     * @param ConfigInterface $config
     * @param ConfigLoaderInterface $configLoader
     * @param ValidatorInterface $validator
     */
    public function __construct(
        DumperInterface $dumper,
        ConfigInterface $config,
        ConfigLoaderInterface $configLoader,
        ValidatorInterface $validator
    ) {
        $this->dumper = $dumper;
        $this->config = $config;
        $this->configLoader = $configLoader;
        $this->validator = $validator;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function configure()
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        $this->setName('dump')
            ->setDescription('Create an anonymized dump')
            ->addOption('driver', null, InputOption::VALUE_OPTIONAL, 'Database driver')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Database host')
            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'Database user')
            ->addOption('password', null, InputOption::VALUE_NONE, 'Whether to prompt a password')
            ->addOption('database', null, InputOption::VALUE_REQUIRED, 'Database name')
            ->addOption('additional-config', null, InputOption::VALUE_REQUIRED, 'JSON-encoded config to load in addition to the configuration file')
            ->addArgument('config_file', InputArgument::OPTIONAL, 'Dump configuration file');
        // phpcs:enable
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $database = (string) $input->getOption('database');
        $configFile = (string) $input->getArgument('config_file');

        try {
            if ($configFile === '' && $database === '') {
                throw new Exception('You must provide a config file or a database name.');
            }

            // Load the config
            $this->loadConfig($input, $output);

            // Validate the config data
            $result = $this->validator->validate($this->config->toArray());

            if (!$result->isValid()) {
                $this->outputValidationResult($result, $output);
                return 1;
            }

            $this->dumper->dump($this->config);
        } catch (Exception $e) {
            if ($output->isVerbose()) {
                throw $e;
            }

            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }

        return 0;
    }

    private function loadConfig(InputInterface $input, OutputInterface $output)
    {
        $configFile = (string) $input->getArgument('config_file');

        // Load the config file
        if ($configFile) {
            $this->configLoader->loadFile($configFile);
        }

        // Load the JSON-encoded config passed in the "additional-config" option
        $this->loadAdditionalConfig($input);

        // Load version-specific data
        $this->configLoader->loadVersionData();

        // Override the config with the console options/arguments
        $this->overrideConfig($input, $output);
    }

    /**
     * Load the additional configuration (JSON-encoded data passed in the "additional-config" option).
     *
     * @param InputInterface $input
     */
    private function loadAdditionalConfig(InputInterface $input)
    {
        $additionalConfig = $input->getOption('additional-config');

        if ($additionalConfig) {
            $decodedData = json_decode($additionalConfig, true);

            if ($decodedData === null) {
                throw new RuntimeException(sprintf('Invalid JSON "%s".', $additionalConfig));
            }

            $this->configLoader->loadData($decodedData);
        }
    }

    /**
     * Override the config with the console arguments/options.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function overrideConfig(InputInterface $input, OutputInterface $output)
    {
        // Database config
        $databaseInput = [
            'host' => $input->getOption('host'),
            'user' => $input->getOption('user'),
            'name' => $input->getOption('database'),
        ];

        $databaseConfig = $this->config->get('database', []);

        foreach ($databaseInput as $key => $value) {
            if ($value !== null) {
                $databaseConfig[$key] = $value;
            }
        }

        // Override password only if it was prompted
        if ($input->getOption('password')) {
            $databaseConfig['password'] = $this->promptPassword($input, $output);
        }

        if (!empty($databaseConfig)) {
            $this->config->set('database', $databaseConfig);
        }
    }

    /**
     * Prompt the user for a password.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    private function promptPassword(InputInterface $input, OutputInterface $output): string
    {
        $helper = $this->getHelper('question');
        $question = new Question('Enter password: ', '');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $password = trim($helper->ask($input, $output, $question));

        return $password;
    }

    /**
     * Display the validation result.
     *
     * @param ValidationResultInterface $result
     * @param OutputInterface $output
     */
    private function outputValidationResult(ValidationResultInterface $result, OutputInterface $output)
    {
        $output->writeln("<error>The following errors were detected:</error>");
        foreach ($result->getMessages() as $message) {
            $output->writeln('  - ' . $message);
        }
    }
}
