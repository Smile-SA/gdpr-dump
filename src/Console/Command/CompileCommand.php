<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console\Command;

use Smile\GdprDump\Phar\Compiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompileCommand extends Command
{
    private string $defaultLocale;

    /**
     * @param string $defaultLocale
     * @param string|null $name
     */
    public function __construct(string $defaultLocale, string $name = null)
    {
        $this->defaultLocale = $defaultLocale;
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName('compiler')
            ->setDescription('Create the phar file')
            ->addOption(
                'locale',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                // phpcs:ignore Generic.Files.LineLength.TooLong
                'Faker locale(s) added to the phar file (e.g. "en_US"). The default locale defined in app/config/services.yaml is always added to the phar file'
            );
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $locales = $input->getOption('locale');
        if (!in_array($this->defaultLocale, $locales, true)) {
            $locales[] = $this->defaultLocale;
        }

        $output->writeln('<comment>Creating the phar file, please wait...</comment>');

        $fileName = $this->getPharFileName();
        $compiler = new Compiler($locales);
        $compiler->compile($fileName);

        $output->writeln('');
        $output->writeln(sprintf('<info>The phar file was created in "%s".</info>', $fileName));
        $output->writeln(
            sprintf('<info>It is bundled with the following Faker locales: %s.</info>', implode(', ', $locales))
        );

        return 0;
    }

    /**
     * Get the file name of the phar archive.
     *
     * @return string
     */
    private function getPharFileName(): string
    {
        $basePath = dirname(__DIR__, 3);

        return $basePath . '/build/dist/gdpr-dump.phar';
    }
}
