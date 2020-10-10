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
        /** @var Finder[] $finders */
        $finders = [
            $this->createFinder($this->basePath . '/src')->name(['*.php']),
            $this->createFinder($this->basePath . '/vendor')->name(['*.php'])->notPath(['bin', 'demo']),
            $this->createFinder($this->basePath . '/app')->notName(['example.yaml']),
        ];

        // Add app, src and vendor directories
        foreach ($finders as $finder) {
            foreach ($finder as $file) {
                $path = $this->getRelativeFilePath($file);
                $phar->addFromString($path, php_strip_whitespace($file->getRealPath()));
            }
        }

        // Add binary file
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $this->basePath . '/bin/gdpr-dump');
        $phar->addFromString('bin/gdpr-dump', php_strip_whitespace($content));
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
