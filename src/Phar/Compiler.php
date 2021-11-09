<?php

declare(strict_types=1);

namespace Smile\GdprDump\Phar;

use Phar;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * @codeCoverageIgnore
 */
class Compiler
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var string[]
     */
    private $locales;

    /**
     * @param string[] $locales
     */
    public function __construct(array $locales)
    {
        $this->basePath = dirname(__DIR__, 2);
        $this->locales = $locales;
    }

    /**
     * Generate a phar file.
     *
     * @param string $fileName
     * @throws RuntimeException
     */
    public function compile(string $fileName): void
    {
        if (file_exists($fileName)) {
            unlink($fileName);
        }

        $directory = pathinfo($fileName, PATHINFO_DIRNAME);
        if (!is_dir($directory)) {
            $this->createDirectory($directory);
        }

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
     * @param Phar $phar
     */
    private function addFiles(Phar $phar): void
    {
        // Add app, src and vendor directories
        foreach ($this->getFinders() as $finder) {
            foreach ($finder as $file) {
                $path = $this->getRelativeFilePath($file);
                $phar->addFromString($path, php_strip_whitespace($file->getRealPath()));
            }
        }

        // Add binary file
        $content = php_strip_whitespace($this->basePath . '/bin/gdpr-dump');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/gdpr-dump', $content);
    }

    /**
     * Create a directory.
     *
     * @param string $path
     * @throws RuntimeException
     */
    private function createDirectory(string $path): void
    {
        if (!mkdir($path, 0775, true)) {
            throw new RuntimeException(sprintf('Failed to create the directory "%s".', $path));
        }
    }

    /**
     * Get the file locators.
     *
     * @return Finder[]
     */
    private function getFinders(): array
    {
        return [
            $this->createFinder($this->basePath . '/src')
                ->name(['*.php']),
            $this->createFinder($this->basePath . '/vendor')
                ->name(['*.php'])
                ->notPath(
                    [
                        'bin/',
                        'justinrainbow/json-schema/demo/',
                        '#fakerphp/faker/src/Faker/Provider/(?!' . implode('|', $this->locales) . ')[a-zA-Z_]+/#',
                    ]
                ),
            $this->createFinder($this->basePath . '/app')
                ->notName(['example.yaml']),
        ];
    }

    /**
     * Create a finder object.
     *
     * @param string $directory
     * @return Finder
     */
    private function createFinder(string $directory): Finder
    {
        $finder = new Finder();

        return $finder->files()
            ->in($directory);
    }

    /**
     * Get the relative path to the file.
     *
     * @param SplFileInfo $file
     * @return string
     */
    private function getRelativeFilePath(SplFileInfo $file): string
    {
        $realPath = $file->getRealPath();

        // Remove the base path of the application from the string
        $pathPrefix = $this->basePath . '/';
        $pos = strpos($realPath, $pathPrefix);
        $relativePath = ($pos !== false) ? substr_replace($realPath, '', $pos, strlen($pathPrefix)) : $realPath;

        return strtr($relativePath, '\\', '/');
    }

    /**
     * Get the phar stub.
     *
     * @return string
     */
    private function getStub(): string
    {
        return <<<EOF
#!/usr/bin/env php
<?php

Phar::interceptFileFuncs();
Phar::mapPhar('gdpr-dump.phar');
require 'phar://gdpr-dump.phar/bin/gdpr-dump';
__HALT_COMPILER();
EOF;
    }
}
