<?php

declare(strict_types=1);

namespace Smile\GdprDump\Phar;

use Phar;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use UnexpectedValueException;

class Compiler
{
    private string $basePath;

    /**
     * @var string[]
     */
    private array $locales;

    /**
     * @param string[] $locales
     */
    public function __construct(array $locales)
    {
        $this->basePath = dirname(__DIR__, 2);
        $this->locales = $locales;

        foreach ($locales as $locale) {
            if (!is_dir($this->basePath . '/vendor/fakerphp/faker/src/Faker/Provider/' . $locale)) {
                throw new UnexpectedValueException(sprintf('Faker does not support the locale "%s".', $locale));
            }
        }
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
     * @param Phar $phar
     */
    private function addFiles(Phar $phar): void
    {
        // Add app, src and vendor directories
        foreach ($this->getFinders() as $finder) {
            foreach ($finder as $file) {
                $path = $this->getRelativeFilePath($file);
                $phar->addFromString($path, $this->stripWhitespaces(file_get_contents($file->getRealPath())));
            }
        }

        // Add binary file
        $content = $this->stripWhitespaces(file_get_contents($this->basePath . '/bin/gdpr-dump'));
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/gdpr-dump', $content);
    }

    /**
     * Strip whitespaces from a PHP source.
     * This function is used instead of php_strip_whitespace, because php_strip_whitespace removes PHP annotations
     * if the version of the PHP runtime is < 8.
     *
     * TODO: use php_strip_whitespace again when the min PHP version of gdpr-dump becomes 8.0.
     *
     * @param string $source
     * @return string
     */
    private function stripWhitespaces(string $source): string
    {
        $output = '';
        $isWhitespace = false;

        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
                $isWhitespace = false;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                // Remove all comments except PHP annotations
                $output .= substr($token[1], 0, 2) === '#[' ? $token[1] : '';
                $isWhitespace = true;
            } elseif ($token[0] === T_WHITESPACE) {
                // Replace everything with a single space (if previous char isn't already a space)
                $output .= !$isWhitespace ? ' ' : '';
                $isWhitespace = true;
            } else {
                $output .= $token[1];
                $isWhitespace = false;
            }
        }

        return $output;
    }

    /**
     * Get the file locators.
     *
     * @return Finder[]
     */
    private function getFinders(): array
    {
        $finder = fn(string $directory) => (new Finder())->files()->in($directory);

        return [
            $finder($this->basePath . '/src')
                ->name(['*.php']),
            $finder($this->basePath . '/vendor')
                ->name(['*.php'])
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
