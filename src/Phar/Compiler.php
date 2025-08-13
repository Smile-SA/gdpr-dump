<?php

declare(strict_types=1);

namespace Smile\GdprDump\Phar;

use Exception;
use Phar;
use RuntimeException;
use Smile\GdprDump\Phar\Minify\MinifierResolver;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

final class Compiler
{
    private string $basePath;

    /**
     * Faker locales to include in the phar file. All locales are included if left empty.
     *
     * @var string[]
     */
    private array $locales = [];

    public function __construct(private MinifierResolver $minifierResolver)
    {
        $this->basePath = dirname(__DIR__, 2);
    }

    /**
     * Set the Faker locales to include.
     *
     * @param string[] $locales
     * @throws RuntimeException
     */
    public function setLocales(array $locales): self
    {
        foreach ($locales as $locale) {
            if (!is_dir($this->basePath . '/vendor/fakerphp/faker/src/Faker/Provider/' . $locale)) {
                throw new RuntimeException(sprintf('Faker does not support the locale "%s".', $locale));
            }
        }

        $this->locales = $locales;

        return $this;
    }

    /**
     * Generate a phar file.
     *
     * @throws Exception
     */
    public function compile(string $fileName): void
    {
        if (file_exists($fileName)) {
            unlink($fileName);
        }

        // Create the build directory if it does not already exist
        $buildDir = pathinfo($fileName, PATHINFO_DIRNAME);
        if (!is_dir($buildDir) && !mkdir($buildDir, 0o775, true)) {
            throw new RuntimeException(sprintf('Failed to create the directory "%s".', $buildDir));
        }

        // Create the phar file
        $phar = new Phar($fileName, 0, 'gdpr-dump.phar');
        $phar->setSignatureAlgorithm(Phar::SHA1);
        $phar->startBuffering();
        $this->addFiles($phar);
        $phar->setStub($this->getStub());
        $phar->stopBuffering();
    }

    /**
     * Add files to the phar file.
     */
    private function addFiles(Phar $phar): void
    {
        // Add binary file
        $contents = $this->parseFile($this->basePath . '/bin/gdpr-dump', 'php');
        $contents = (string) preg_replace('{^#!/usr/bin/env php\s*}', '', $contents);
        $phar->addFromString('bin/gdpr-dump', $contents);

        // Add app, src, var and vendor directories
        foreach ($this->getFinders() as $finder) {
            foreach ($finder as $file) {
                $path = $this->getRelativeFilePath($file);
                $phar->addFromString($path, $this->parseFile($file->getRealPath()));
            }
        }
    }

    /**
     * Get the file locators.
     *
     * @return Finder[]
     */
    private function getFinders(): array
    {
        $vendorFinder = (new Finder())
            ->files()
            ->in($this->basePath . '/vendor')
            // The directory "vendor/symfony/console/Resources" (which stores shell completion files) must exist
            ->name(['*.php', 'completion.*']);

        if ($this->locales) {
            $vendorFinder->notPath([
                'bin/',
                '#fakerphp/faker/src/Faker/Provider/(?!' . implode('|', $this->locales) . ')[a-zA-Z_]+/#',
            ]);
        }

        return [
            (new Finder())->files()->in($this->basePath . '/app')->notName(['example.yaml']),
            (new Finder())->files()->in($this->basePath . '/src')->name(['*.php']),
            (new Finder())->files()->in($this->basePath . '/var')->name(['container_cache*'])->depth(0),
            $vendorFinder,
        ];
    }

    /**
     * Get the relative path to the file.
     */
    private function getRelativeFilePath(SplFileInfo $file): string
    {
        $realPath = $file->getRealPath();

        // Remove the base path of the application from the string
        $pathPrefix = $this->basePath . '/';
        $pos = strpos($realPath, $pathPrefix);
        $relativePath = $pos !== false ? substr_replace($realPath, '', $pos, strlen($pathPrefix)) : $realPath;

        return strtr($relativePath, '\\', '/');
    }

    /**
     * Read and minify the contents of a file.
     */
    private function parseFile(string $fileName, ?string $extension = null): string
    {
        $contents = file_get_contents($fileName);
        if ($contents === false) {
            throw new RuntimeException(sprintf('Failed to open the file "%s".', $fileName));
        }

        $extension ??= pathinfo($fileName, PATHINFO_EXTENSION);
        $minifier = $this->minifierResolver->getMinifier($extension);

        return $minifier ? $minifier->minify($contents) : $contents;
    }

    /**
     * Get the phar stub.
     */
    private function getStub(): string
    {
        return <<<'EOT'
#!/usr/bin/env php
<?php

Phar::interceptFileFuncs();
Phar::mapPhar('gdpr-dump.phar');
require 'phar://gdpr-dump.phar/bin/gdpr-dump';
__HALT_COMPILER();
EOT;
    }
}
