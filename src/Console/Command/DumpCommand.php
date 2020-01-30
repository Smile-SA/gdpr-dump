<?php
declare(strict_types=1);

namespace Smile\GdprDump\Console\Command;

use Exception;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Config\ConfigLoaderInterface;
use Smile\GdprDump\Config\Validator\ValidationResultInterface;
use Smile\GdprDump\Config\Validator\ValidatorInterface;
use Smile\GdprDump\Config\Version\VersionLoaderInterface;
use Smile\GdprDump\Dumper\DumperInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
     * @var VersionLoaderInterface
     */
    private $configVersionLoader;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param DumperInterface $dumper
     * @param ConfigInterface $config
     * @param ConfigLoaderInterface $configLoader
     * @param VersionLoaderInterface $configVersionLoader
     * @param ValidatorInterface $validator
     */
    public function __construct(
        DumperInterface $dumper,
        ConfigInterface $config,
        ConfigLoaderInterface $configLoader,
        VersionLoaderInterface $configVersionLoader,
        ValidatorInterface $validator
    ) {
        $this->dumper = $dumper;
        $this->config = $config;
        $this->configLoader = $configLoader;
        $this->configVersionLoader = $configVersionLoader;
        $this->validator = $validator;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function configure()
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        $this->setName('gdpr-dump')
            ->setDescription('Create an anonymized dump')
            ->addArgument('config_file', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Dump configuration file(s)');
        // phpcs:enable
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // Load the config
            $this->loadConfig($input);

            // Prompt the password if required
            $database = $this->config->get('database');
            if (!isset($database['password'])) {
                $password = $this->promptPassword($input, $output);
                $database['password'] = $password;
                $this->config->set('database', $database);
            }

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

    /**
     * Load the dump config.
     *
     * @param InputInterface $input
     */
    private function loadConfig(InputInterface $input)
    {
        // Load the config file(s)
        $configFiles = $input->getArgument('config_file');

        if (!empty($configFiles)) {
            foreach ($configFiles as $configFile) {
                $this->configLoader->loadFile($configFile);
            }
        }

        // Load version-specific data
        $this->configVersionLoader->load($this->config);
    }

    /**
     * Display a password prompt, and return the user input.
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

        return trim($helper->ask($input, $output, $question));
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
