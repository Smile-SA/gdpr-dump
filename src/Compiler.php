<?php
declare(strict_types=1);

namespace Smile\Anonymizer;

use Symfony\Component\Finder\Finder;

class Compiler
{
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

        $phar = new \Phar($fileName, 0, 'anonymizer.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);
        $phar->startBuffering();

        // Add source files
        $this->addFiles($phar, APP_ROOT . '/src', ['*.php']);

        // Add vendor files
        $this->addFiles($phar, APP_ROOT . '/vendor', ['*.php'], $this->getExcludedVendorDirs());

        // Add config files
        $this->addFiles($phar, APP_ROOT . '/config');

        // Add bin file
        $this->addConsoleBin($phar);

        $phar->setStub($this->getStub());
        $phar->stopBuffering();
    }

    /**
     * Add files to the phar file.
     *
     * @param \Phar $phar
     * @param string $directory
     * @param string[] $patterns
     * @param string[] $exclude
     */
    private function addFiles(\Phar $phar, string $directory, array $patterns = [], array $exclude = [])
    {
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->exclude($exclude)
            ->in($directory)
            ->sort(function (\SplFileInfo $a, \SplFileInfo $b) {
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
     * @param \Phar $phar
     * @param \SplFileInfo $file
     */
    private function addFile(\Phar $phar, \SplFileInfo $file)
    {
        // Path must be relative
        $path = $this->getRelativeFilePath($file);

        // Strip whitespace before adding the file
        $content = php_strip_whitespace(APP_ROOT . '/' . $path);

        $phar->addFromString($path, $content);
    }

    /**
     * Add anonymizer bin to the phar file.
     *
     * @param \Phar $phar
     */
    private function addConsoleBin(\Phar $phar)
    {
        $content = php_strip_whitespace(APP_ROOT . '/bin/console');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/console', $content);
    }

    /**
     * Get the relative path to the file.
     *
     * @param \SplFileInfo $file
     * @return string
     */
    private function getRelativeFilePath(\SplFileInfo $file): string
    {
        $realPath = $file->getRealPath();
        $pathPrefix = APP_ROOT . DIRECTORY_SEPARATOR;
        $pos = strpos($realPath, $pathPrefix);
        $relativePath = ($pos !== false) ? substr_replace($realPath, '', $pos, strlen($pathPrefix)) : $realPath;

        return strtr($relativePath, '\\', '/');
    }

    /**
     * Get the vendor directories to exclude.
     *
     * @return string[]
     */
    private function getExcludedVendorDirs()
    {
        return ['Tests', 'tests', 'test', 'unit-tests', 'fixtures', 'examples', 'build'];
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
Phar::mapPhar('anonymizer.phar');
require 'phar://anonymizer.phar/bin/console';
__HALT_COMPILER();
EOF;
    }
}
