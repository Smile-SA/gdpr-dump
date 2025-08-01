<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console\Command;

use Smile\GdprDump\Phar\Compiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CompileCommand extends Command
{
    public function __construct(private Compiler $compiler, private string $defaultLocale)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setName('compiler')
            ->setDescription('Create the phar file')
            ->addOption(
                'locale',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                // phpcs:ignore Generic.Files.LineLength.TooLong
                'Faker locale(s) added to the phar file (e.g. "en_US"). If omitted, all locales will be included'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->hasDevPackages()) {
            $output->writeln('<error>Dev packages detected. Please run "composer install --no-dev".</error>');
            return Command::FAILURE;
        }

        /** @var string[] $locales */
        $locales = $input->getOption('locale');
        if ($locales && !in_array($this->defaultLocale, $locales, true)) {
            // phpcs:ignore Generic.Files.LineLength.TooLong
            $output->writeln(sprintf('<error>Cannot proceed without including the default locale "%s" defined in app/config/services.yaml.</error>', $this->defaultLocale));
            return Command::FAILURE;
        }

        $output->writeln('<comment>Creating the phar file, please wait...</comment>');

        $fileName = $this->getPharFileName();
        $this->compiler->setLocales($locales)
            ->compile($fileName);

        $output->writeln('');
        $output->writeln(sprintf('<info>The phar file was created in "%s".</info>', $fileName));

        $localesMsg = $locales
            ? sprintf('It is bundled with the following Faker locales: %s', implode(', ', $locales))
            : 'It is bundled with all Faker locales';
        $output->writeln(sprintf('<info>%s. The default locale is "%s".</info>', $localesMsg, $this->defaultLocale));

        return Command::SUCCESS;
    }

    /**
     * Check whether composer dev packages are installed.
     */
    private function hasDevPackages(): bool
    {
        return is_dir(dirname(__DIR__, 3) . '/vendor/phpunit');
    }

    /**
     * Get the file name of the phar archive.
     */
    private function getPharFileName(): string
    {
        $basePath = dirname(__DIR__, 3);

        return $basePath . '/build/dist/gdpr-dump.phar';
    }
}
