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

        // Add anonymizer library
        $this->addFiles($phar, __DIR__, ['*.php'], ['Tests', 'tests', 'test']);

        // Add vendor libraries
        $this->addFiles($phar, __DIR__ . '/../vendor', ['*.php'], ['Tests', 'test']);

        // Add bin file
        $this->addAnonymizerBin($phar);

        $phar->setStub($this->getStub());
        $phar->stopBuffering();
    }

    /**
     * Add files to the phar file.
     *
     * @param \Phar $phar
     * @param string $directory
     * @param array $patterns
     * @param array $exclude
     */
    private function addFiles(\Phar $phar, string $directory, array $patterns, array $exclude = [])
    {
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name($patterns)
            ->exclude($exclude)
            ->in($directory)
            ->sort(\Closure::fromCallable([$this, 'sortFiles']));

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }
    }

    /**
     * Sort files by path.
     *
     * @param \SplFileInfo $a
     * @param \SplFileInfo $b
     * @return int
     */
    private function sortFiles(\SplFileInfo $a, \SplFileInfo $b): int
    {
        return strcmp(strtr($a->getRealPath(), '\\', '/'), strtr($b->getRealPath(), '\\', '/'));
    }

    /**
     * Add a file to the phar file.
     *
     * @param \Phar $phar
     * @param \SplFileInfo $file
     */
    private function addFile(\Phar $phar, \SplFileInfo $file)
    {
        $path = $this->getRelativeFilePath($file);
        $content = php_strip_whitespace($path);

        $phar->addFromString($path, $content);
    }

    /**
     * Add anonymizer bin to the phar file.
     *
     * @param \Phar $phar
     */
    private function addAnonymizerBin(\Phar $phar)
    {
        $content = php_strip_whitespace(__DIR__ . '/../bin/dump');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/dump', $content);
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
        $pathPrefix = dirname(__DIR__) . DIRECTORY_SEPARATOR;
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
Phar::mapPhar('anonymizer.phar');
require 'phar://anonymizer.phar/bin/dump';
__HALT_COMPILER();
EOF;
    }
}
