<?php

declare(strict_types=1);

namespace Smile\GdprDump;

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
     * Constructor.
     */
    public function __construct()
    {
        $this->basePath = dirname(__DIR__);
    }

    /**
     * Generate a phar file.
     *
     * @param string $fileName
     */
    public function compile(string $fileName)
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

        // Add source files
        $this->addFiles($phar, $this->basePath . '/src', ['*.php']);

        // Add vendor files
        $this->addFiles($phar, $this->basePath . '/vendor', ['*.php'], $this->getExcludedVendorDirs());

        // Add application files
        $this->addFiles($phar, $this->basePath . '/app');

        // Add bin file
        $this->addConsoleBin($phar);

        $phar->setStub($this->getStub());
        $phar->stopBuffering();
    }

    /**
     * Add files to the phar file.
     *
     * @param Phar $phar
     * @param string $directory
     * @param string[] $patterns
     * @param string[] $exclude
     */
    private function addFiles(Phar $phar, string $directory, array $patterns = [], array $exclude = [])
    {
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->exclude($exclude)
            ->in($directory)
            ->sort(function (SplFileInfo $a, SplFileInfo $b) {
                return strcmp(strtr($a->getRealPath(), '\\', '/'), strtr($b->getRealPath(), '\\', '/'));
            });

        foreach ($patterns as $pattern) {
            $finder->name($pattern);
        }

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }
    }

    /**
     * Add a file to the phar file.
     *
     * @param Phar $phar
     * @param SplFileInfo $file
     */
    private function addFile(Phar $phar, SplFileInfo $file)
    {
        // Path must be relative
        $path = $this->getRelativeFilePath($file);

        // Strip whitespace before adding the file
        $content = php_strip_whitespace($file->getRealPath());

        $phar->addFromString($path, $content);
    }

    /**
     * Add console binary to the phar file.
     *
     * @param Phar $phar
     */
    private function addConsoleBin(Phar $phar)
    {
        $content = php_strip_whitespace($this->basePath . '/bin/gdpr-dump');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/gdpr-dump', $content);
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
     * Create a directory.
     *
     * @param string $path
     * @throws RuntimeException
     */
    private function createDirectory(string $path)
    {
        if (!mkdir($path, 0775, true)) {
            throw new RuntimeException(sprintf('Failed to create the directory "%s".', $path));
        }
    }

    /**
     * Get the vendor directories to exclude.
     *
     * @return string[]
     */
    private function getExcludedVendorDirs(): array
    {
        return [
            'bin', // composer, doctrine/*
            'demo', // justinrainbow/json-schema
            'unit-tests', // ifsnop/mysqldump-php
            'tests', // doctrine/*, theseer/tokenizer
            'test', // fzaninotto/faker,
            'Tests', // symfony/*
        ];
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
