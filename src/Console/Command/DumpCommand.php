<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Console\Command;

use Smile\Anonymizer\Config\ConfigInterface;
use Smile\Anonymizer\Config\ConfigLoader;
use Smile\Anonymizer\Config\Validator\ValidationResultInterface;
use Smile\Anonymizer\Config\Validator\ValidatorInterface;
use Smile\Anonymizer\Dumper\DumperInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

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
     * @var ConfigLoader
     */
    private $configLoader;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param DumperInterface $dumper
     * @param ConfigInterface $config
     * @param ConfigLoader $configLoader
     * @param ValidatorInterface $validator
     */
    public function __construct(
        DumperInterface $dumper,
        ConfigInterface $config,
        ConfigLoader $configLoader,
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
        $this->setName('dump')
            ->setDescription('Create an anonymized dump')
            ->addOption('driver', null, InputOption::VALUE_OPTIONAL, 'Database driver')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Database host')
            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'Database user')
            ->addOption('password', null, InputOption::VALUE_NONE, 'Whether to prompt a password')
            ->addOption('database', null, InputOption::VALUE_REQUIRED, 'Database name')
            ->addArgument('config_file', InputArgument::OPTIONAL, 'Dump configuration file');
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $prompt = $input->getOption('password');
        $configFile = $input->getArgument('config_file');

        try {
            // Get the password
            $password = $prompt ? $this->promptPassword($input, $output) : '';

            // Load the config
            if ($configFile) {
                $this->configLoader->load($configFile);
            }

            // Override the config with the console options/arguments
            $this->overrideConfig($input, $password);

            // Validate the config data
            $result = $this->validator->validate($this->config->toArray());

            if (!$result->isValid()) {
                $this->outputValidationResult($result, $output);
                return 1;
            }

            $this->dumper->dump($this->config);
        } catch (\Exception $e) {
            if ($output->isVerbose()) {
                throw $e;
            }

            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }

        return 0;
    }

    /**
     * Override the config with the console arguments/options.
     *
     * @param InputInterface $input
     * @param string $password
     */
    private function overrideConfig(InputInterface $input, string $password)
    {
        $values = [
            'host' => $input->getOption('host'),
            'user' => $input->getOption('user'),
            'name' => $input->getOption('database'),
        ];

        $databaseData = $this->config->get('database', []);

        foreach ($values as $key => $value) {
            if ($value !== null) {
                $databaseData[$key] = $value;
            }
        }

        if ($password !== '') {
            $databaseData['password'] = $password;
        }

        $this->config->set('database', $databaseData);
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
