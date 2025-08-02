<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console\Command;

use Faker\Factory;
use RuntimeException;
use Smile\GdprDump\Phar\Compiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class CompileCommand extends Command
{
    public function __construct(private Compiler $compiler, private string $defaultLocale)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setName('compiler')
            ->setDescription('Create a phar file in build/gdpr-dump.phar')
            ->addOption(
                'locale',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                // phpcs:ignore Generic.Files.LineLength.TooLong
                'Faker locale(s) to bundle with the phar file (e.g. "en_US"). If omitted, all locales will be included'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($this->hasDevPackages()) {
                $output->writeln('<error>Dev packages detected. Please run "composer install --no-dev".</error>');
                return Command::FAILURE;
            }

            $locales = $this->getLocales($input);
            $output->writeln('<comment>Creating the phar file, please wait...</comment>');

            $fileName = $this->getPharFileName();
            $this->compiler->setLocales($locales)
                ->compile($fileName);

            $output->writeln('');
            $output->writeln(sprintf('<info>The phar file was created in "%s".</info>', $fileName));

            $localeMsg = $locales
                ? sprintf('It is bundled with the following Faker locales: %s', implode(', ', $locales))
                : 'It is bundled with all Faker locales';
            $output->writeln(sprintf('<info>%s. The default locale is "%s".</info>', $localeMsg, $this->defaultLocale));
        } catch (Throwable $e) {
            if ($output->isVerbose()) {
                throw $e;
            }

            $this->getErrorOutput($output)->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Get the locales to include in the phar file. Returns an empty array if all locales must be included.
     *
     * @return string[]
     */
    private function getLocales(InputInterface $input): array
    {
        /** @var string[] $locales */
        $locales = $input->getOption('locale');

        if ($locales) {
            // Throw an exception if one of the locales is not supported
            $this->validateLocales($locales);

            if (!in_array($this->defaultLocale, $locales, true)) {
                // phpcs:ignore Generic.Files.LineLength.TooLong
                throw new RuntimeException(sprintf('Cannot proceed without including the default locale "%s" defined in app/config/services.yaml.', $this->defaultLocale));
            }

            // Always include the fallback Faker locale
            if (!in_array(Factory::DEFAULT_LOCALE, $locales, true)) {
                $locales[] = Factory::DEFAULT_LOCALE;
            }
        }

        return $locales;
    }

    /**
     * Assert that the specified local are defined.
     *
     * @param string[] $locales
     */
    private function validateLocales(array $locales): void
    {
        $basePath = dirname(__DIR__, 3);

        foreach ($locales as $locale) {
            if (!is_dir($basePath . '/vendor/fakerphp/faker/src/Faker/Provider/' . $locale)) {
                throw new RuntimeException(sprintf('The locale "%s" is not supported by Faker.', $locale));
            }
        }
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
        return dirname(__DIR__, 3) . '/build/gdpr-dump.phar';
    }

    /**
     * Get the error output.
     */
    private function getErrorOutput(OutputInterface $output): OutputInterface
    {
        return $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
    }
}
