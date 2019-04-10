<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Console\Command;

use Smile\Anonymizer\Config\ConfigInterface;
use Smile\Anonymizer\Config\ConfigLoader;
use Smile\Anonymizer\Config\Validator\ValidatorInterface;
use Smile\Anonymizer\Config\Validator\ValidationResultInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateConfigCommand extends Command
{
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
     * @param ConfigInterface $config
     * @param ConfigLoader $configLoader
     * @param ValidatorInterface $validator
     */
    public function __construct(
        ConfigInterface $config,
        ConfigLoader $configLoader,
        ValidatorInterface $validator
    ) {
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
        $this->setName('config:validate')
            ->setDescription('Validate a config file')
            ->addArgument('config_file', InputArgument::REQUIRED, 'Path to the config file');
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $fileName = $input->getArgument('config_file');

        try {
            // Load the data
            $this->configLoader->load($fileName);

            // Validate the data against the schema
            $data = $this->config->toArray();
            $result = $this->validator->validate($data);

            // Output the results
            $this->outputValidationResult($result, $output);
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
     * Display the validation result.
     *
     * @param ValidationResultInterface $result
     * @param OutputInterface $output
     */
    private function outputValidationResult(ValidationResultInterface $result, OutputInterface $output)
    {
        if ($result->isValid()) {
            $output->writeln('<info>The file is valid.</info>');
        } else {
            $output->writeln("<error>The following errors were detected:</error>");
            foreach ($result->getMessages() as $message) {
                $output->writeln('  - ' . $message);
            }
        }
    }
}
