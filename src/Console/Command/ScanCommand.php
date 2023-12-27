<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console\Command;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Exception;
use Smile\GdprDump\Config\Compiler\CompilerInterface;
use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\ConfigException;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Config\Loader\ConfigLoaderInterface;
use Smile\GdprDump\Config\Validator\ValidationResultInterface;
use Smile\GdprDump\Config\Validator\ValidatorInterface;
use Smile\GdprDump\Database\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class ScanCommand extends Command
{
    private InputInterface $input;
    protected OutputInterface $output;
    private ConfigInterface $config;
    private Database $database;

    public function __construct(
        private readonly ConfigLoaderInterface $configLoader,
        private readonly ValidatorInterface $validator,
        private readonly CompilerInterface $compiler
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('scan')
            ->setDescription('Scan for columns containing personally identifiable information')
            ->addArgument(
                'config_file',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Dump configuration file(s)'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->input = $input;

        try {
            // Load the config
            $this->config = $this->loadConfig($input);

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

            $this->database = $this->getDatabase($this->config);

            $this->scan();
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
        $this->configLoader->setConfig($config);
        foreach ($input->getArgument('config_file') as $configFile) {
            $this->configLoader->load($configFile);
        }
        $this->configLoader->load('app/config/scan.yaml');

        // Compile the config
        $this->compiler->compile($config);

        return $config;
    }

    /**
     * @return int
     * @throws \Doctrine\DBAL\Exception
     */
    protected function scan(): int
    {
        $sm = $this->database->getConnection()->createSchemaManager();
        $columns = $this->getTableColumns($sm);

        $candidates = [];
        foreach ($columns as $column) {
            list ($tableName, $columnName) = explode('.', $column);
            if ($suggestedConverter = $this->getSuggestedConverter($this->striposa($columnName, $this->config->get('scan_identifiers')))) {
                $examples = $this->getExamples($this->database->getConnection(), $tableName, $columnName);
                $candidates[] = [$tableName, $columnName, $suggestedConverter, implode(', ', $examples)];
            }
        }

        $candidatesTable = new Table($this->output);
        $candidatesTable->setHeaders(['Table', 'Column', 'Suggested converter', 'Example values']);
        $candidatesTable->setRows($candidates);
        $candidatesTable->render();

        $configuration = [];

        foreach ($candidates as $candidate) {
            list($table, $columnName, $suggestedConverter, $examples) = $candidate;
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                sprintf(
                    "<comment>Example values: %s</comment>\nDo you want to add <options=bold>%s</> with converter <options=bold>%s</>?</question> <info>%s</info> ",
                    empty($examples) ? 'None' : print_r($examples, true),
                    $table . '.' . $columnName,
                    $suggestedConverter,
                    !empty($examples) ? '[Y/n]' : '[y/N]'
                ),
                !empty($examples)
            );

            if ($helper->ask($this->input, $this->output, $question)) {
                if (!isset($configuration[$table])) {
                    $configuration[$table] = ['converters' => []];
                }

                if (!isset($configuration[$table]['converters'][$columnName])) {
                    $configuration[$table]['converters'][$columnName]['converter'] = $suggestedConverter;
                }
            }
        }

        if ($configuration) {
            $this->output->writeln(PHP_EOL . 'Add this to your gdpr-dump.yaml under \'tables\':' . PHP_EOL);
            $this->output->writeln(Yaml::dump($configuration, 4, 2));
            $this->output->writeln(PHP_EOL . 'Check the above output! It merely does some suggestions for the converters.');
        }

        return 0;
    }

    /**
     * @param $haystack
     * @param $needle
     * @param int $offset
     * @return string
     */
    protected function striposa($haystack, $needle, int $offset = 0): string
    {
        if (!is_array($needle)) {
            $needle = array($needle);
        }
        foreach ($needle as $query) {
            if (stripos($haystack, $query, $offset) !== false) {
                return $query;
            }
        }
        return '';
    }

    /**
     * @param ConfigInterface $config
     * @return Database
     * @throws \Doctrine\DBAL\Exception
     */
    private function getDatabase(ConfigInterface $config): Database
    {
        $connectionParams = $config->get('database', []);

        // Rename some keys (for compatibility with the Doctrine connection)
        if (array_key_exists('name', $connectionParams)) {
            $connectionParams['dbname'] = $connectionParams['name'];
            unset($connectionParams['name']);
        }

        if (array_key_exists('driver_options', $connectionParams)) {
            $connectionParams['driverOptions'] = $connectionParams['driver_options'];
            unset($connectionParams['driver_options']);
        }

        return new Database($connectionParams);
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

    /**
     * @param AbstractSchemaManager $sm
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    private function getTableColumns(AbstractSchemaManager $sm): array
    {
        $tables = $sm->listTables();
        $tableColumns = [];
        foreach ($tables as $table) {
            foreach ($sm->listTableColumns($table->getName()) as $column) {
                $tableColumns[] = $table->getName() . '.' . $column->getName();
            }
        }

        $configuredTableColumns = [];
        foreach ($this->config->get('tables') as $tableName => $table) {
            if (!isset($table['converters'])) { // If no converters are given, add all columns
                $columns = array_map(function ($table) {
                    return $table->getName();
                }, $sm->listTableColumns($tableName));
            } else {
                $columns = array_keys($table['converters']);
            }

            foreach ($columns as $columnName) {
                $configuredTableColumns[] = $tableName . '.' . $columnName;
            }
        }

        $excludedTableColumns = array_merge($configuredTableColumns, $this->config->get('magento2_core_columns'));

        return array_diff($tableColumns, $excludedTableColumns);
    }

    /**
     * @param string $string
     * @return string
     */
    private function getSuggestedConverter(string $string): string
    {
        if (empty($string)) {
            return '';
        }

        return match (true) {
            str_contains($string, 'email') => 'randomizeEmail',
            str_contains($string, 'name'),
            str_contains($string, 'city') => 'anonymizeText',
            str_contains($string, 'number'),
            str_contains($string, 'postcode') => 'randomizeNumber',
            str_contains($string, 'ip_address'),
            str_contains($string, 'remote_ip') => 'faker',
            default => 'randomizeText',
        };
    }

    /**
     * @param $connection
     * @param string $tableName
     * @param string $columnName
     * @return array
     */
    private function getExamples($connection, string $tableName, string $columnName): array
    {
        $sql = sprintf(
            'SELECT DISTINCT %s FROM %s WHERE %s IS NOT NULL ORDER BY RAND() LIMIT 3',
            $columnName,
            $tableName,
            $columnName
        );
        $stmt = $connection->executeQuery($sql);
        $results = $stmt->fetchAll();

        return array_filter(array_map(function ($row) use ($columnName) {
            if (!is_string($row[$columnName])) return false;
            $string = $row[$columnName];
            if (strlen($string) > 30) {
                $string = substr($string, 0, 30) . '...';
            }
            return utf8_encode($string) === $string ? $string : false;
        }, $results));
    }
}
