<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Command;

use Faker;
use Ifsnop\Mysqldump\Mysqldump;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class DumpCommand extends Command
{
    /**
     * @var Faker\Generator
     */
    private $faker;

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName('dump')
            ->setDescription('Create an anonymized database dump')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Database host', 'localhost')
            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'Database user', 'root')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Database password')
            ->addArgument('db_name', InputArgument::REQUIRED, 'Database name')
            ->addArgument('dump_file', InputArgument::OPTIONAL, 'Dump file');
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getOption('host');
        $user = $input->getOption('user');
        $password = $input->getOption('password');
        $database = $input->getArgument('db_name');
        $dumpFile = $input->getArgument('dump_file');

        if ($password === null) {
            $password = $this->promptPassword($input, $output);
        }

        // In the final application, it should be possible to change the dump settings,
        // with command-line options and/or config values
        $settings = [
            'add-drop-table' => true,
        ];

        $this->faker = Faker\Factory::create('fr_FR');

        // The driver is hardcoded, this will need to change in the final application
        $dumper = new Mysqldump("mysql:host=$host;dbname=$database", $user, $password, $settings);
        $dumper->setTransformColumnValueHook([$this, 'anonymize']);
        $dumper->start($dumpFile);
    }

    /**
     * In this proof-of-concept, we only anonymize the customer_entity table of Magento 2,
     * and don't use any abstraction layer.
     * Of course, this will need to be changed in the final application.
     *
     * @param string $table
     * @param string $column
     * @param string|null $value
     * @return string|null
     */
    public function anonymize(string $table, string $column, $value)
    {
        // In this proof-of-concept, we only anonymize the customer_entity table of Magento 2,
        // and don't use any abstraction layer to perform the anonymization
        // Of course, this will need to be changed in the final application

        if ($value === null || $value === '') {
            return $value;
        }

        if ($table === 'customer_entity') {
            if ($column === 'email') {
                return $this->faker->email;
            }
            if ($column === 'firstname') {
                return $this->faker->firstName;
            }
            if ($column === 'lastname') {
                return $this->faker->lastName;
            }
            if ($column === 'middlename') {
                return null;
            }
        }

        return $value;
    }

    /**
     * Prompt the user for a password.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     * @throws \Exception
     */
    private function promptPassword(InputInterface $input, OutputInterface $output): string
    {
        $helper = $this->getHelper('question');
        $question = new Question('Enter password: ', '');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $password = trim($helper->ask($input, $output, $question));

        if ($password === '' && !$this->promptEmptyPassword($input, $output)) {
            return $this->promptPassword($input, $output);
        }

        return $password;
    }

    /**
     * Prompt the user for a confirmation that an empty password is what he wanted.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws \Exception
     */
    private function promptEmptyPassword(InputInterface $input, OutputInterface $output): bool
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            'The provided password is empty. Proceed with an empty password? [y/n] ',
            false
        );

        return $helper->ask($input, $output, $question);
    }
}
