<?php
declare(strict_types=1);

namespace Smile\GdprDump\Console\Command;

use Exception;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Config\ConfigLoaderInterface;
use Smile\GdprDump\Config\Validator\ValidationResultInterface;
use Smile\GdprDump\Config\Validator\ValidatorInterface;
use Smile\GdprDump\Dumper\DumperInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $this->configLoader->loadVersionData();
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
