<?php
namespace Smile\Anonymizer\Command;

use Faker;
use Ifsnop\Mysqldump\Mysqldump;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class AnonymizeCommand extends Command
{
    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName('anonymize')
            ->setDescription('Anonymize data')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Database host', 'localhost')
            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'Database user', 'root')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Database password')
            ->addArgument('database', InputArgument::REQUIRED, 'Database password');
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getOption('host');
        $user = $input->getOption('user');
        $password = $input->getOption('password');
        $database = $input->getArgument('database');

        if ($password === null) {
            $password = $this->promptPassword($input, $output);
        }

        $settings = [
            'add-drop-table' => true,
        ];

        $faker = Faker\Factory::create('fr_FR');
        $dumper = new Mysqldump("mysql:host=$host;dbname=$database", $user, $password, $settings);

        $dumper->setTransformColumnValueHook(function ($table, $column, $value) use ($faker) {
            if ($table === 'customer_entity' && $column === 'email') {
                return $faker->email;
            }

            return $value;
        });

        $dumper->start('dump.sql');
    }

    /**
     * Prompt the user for a password.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     * @throws \Exception
     */
    private function promptPassword(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new Question('Enter password: ', '');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $password = $helper->ask($input, $output, $question);

        if ($password === '') {
            throw new \Exception('Empty password provided.');
        }

        return $password;
    }
}
