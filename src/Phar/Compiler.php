<?php

declare(strict_types=1);

namespace Smile\GdprDump\Phar;

use Phar;
use RuntimeException;
use Smile\GdprDump\Phar\Minify\MinifierInterface;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use UnexpectedValueException;

class Compiler
{
    private string $basePath;

    /**
     * @var string[]
     */
    private array $locales = [];

    /**
     * @param MinifierInterface[] $minifiers
     */
    public function __construct(private iterable $minifiers = [])
    {
        $this->basePath = dirname(__DIR__, 2);
    }

    /**
     * Set the Faker locales to include.
     *
     * @throws UnexpectedValueException
     */
    public function setLocales(array $locales): self
    {
        foreach ($locales as $locale) {
            if (!is_dir($this->basePath . '/vendor/fakerphp/faker/src/Faker/Provider/' . $locale)) {
                throw new UnexpectedValueException(sprintf('Faker does not support the locale "%s".', $locale));
            }
        }

        $this->locales = $locales;

        return $this;
    }

    /**
     * Generate a phar file.
     *
     * @throws RuntimeException
     */
    public function compile(string $fileName): void
    {
        if (file_exists($fileName)) {
            unlink($fileName);
        }

        // Create the build directory if it does not already exist
        $buildDir = pathinfo($fileName, PATHINFO_DIRNAME);
        if (!is_dir($buildDir) && !mkdir($buildDir, 0775, true)) {
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
     *
     * @throws RuntimeException
     */
    private function addFiles(Phar $phar): void
    {
        // Add app, src and vendor directories
        foreach ($this->getFinders() as $finder) {
            foreach ($finder as $file) {
                $path = $this->getRelativeFilePath($file);
                $phar->addFromString($path, $this->parseFile($file->getRealPath()));
            }
        }

        // Add binary file
        $contents = $this->parseFile($this->basePath . '/bin/gdpr-dump', 'php');
        $contents = (string) preg_replace('{^#!/usr/bin/env php\s*}', '', $contents);
        $phar->addFromString('bin/gdpr-dump', $contents);
    }

    /**
     * Get the file locators.
     *
     * @return Finder[]
     */
    private function getFinders(): array
    {
        $finder = fn (string $directory) => (new Finder())->files()->in($directory);

        return [
            $finder($this->basePath . '/src')
                ->name(['*.php']),
            $finder($this->basePath . '/vendor')
                // The directory "vendor/symfony/console/Resources" (which stores shell completion files) must exist,
                // otherwise the generated phar fails to run
                ->name(['*.php', 'completion.*'])
                ->notPath(
                    [
                        'bin/',
                        '#fakerphp/faker/src/Faker/Provider/(?!' . implode('|', $this->locales) . ')[a-zA-Z_]+/#',
                    ]
                ),
            $finder($this->basePath . '/app')
                ->notName(['example.yaml']),
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
     *
     * @throws RuntimeException
     */
    private function parseFile(string $fileName, ?string $extension = null): string
    {
        $contents = file_get_contents($fileName);
        if ($contents === false) {
            throw new RuntimeException(sprintf('Failed to open the file "%s".', $fileName));
        }

        if ($extension === null) {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        }

        foreach ($this->minifiers as $minifier) {
            if ($minifier->supports($extension)) {
                $contents = $minifier->minify($contents);
                break;
            }
        }

        return $contents;
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
