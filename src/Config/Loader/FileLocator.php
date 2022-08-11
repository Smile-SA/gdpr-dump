<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Loader;

class FileLocator implements FileLocatorInterface
{
    /**
     * @var string[]
     */
    private ?array $templates = null;

    public function __construct(private string $templatesDirectory)
    {
    }

    /**
     * @inheritdoc
     */
    public function locate(string $path, ?string $currentDirectory = null): string
    {
        if ($this->templates === null) {
            $this->templates = $this->getTemplates();
        }

        // Check if it is a config template
        if (array_key_exists($path, $this->templates)) {
            return $this->templates[$path];
        }

        // Absolute path: check if file exists and return the path
        if ($this->isAbsolutePath($path)) {
            if (!file_exists($path)) {
                throw new FileNotFoundException(sprintf('The file "%s" was not found.', $path));
            }

            return $path;
        }

        // Append the current path if specified
        if ($currentDirectory !== null) {
            $path = $currentDirectory . '/' . $path;
        }

        // Get the absolute path (to ensure compatibility with phar file)
        return $this->realpath($path);
    }

    /**
     * Locate the config templates.
     *
     * @throws FileNotFoundException
     */
    private function getTemplates(): array
    {
        // Can't use glob, doesn't work with phar
        $files = scandir($this->templatesDirectory);
        if ($files === false) {
            throw new FileNotFoundException(sprintf('Failed to scan the directory "%s".', $this->templatesDirectory));
        }

        $templates = [];

        foreach ($files as $fileName) {
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }

            $template = pathinfo($fileName, PATHINFO_FILENAME);
            $templates[$template] = $this->templatesDirectory . '/' . $fileName;
        }

        return $templates;
    }

    /**
     * Get the absolute path of a file.
     *
     * @throws FileNotFoundException
     */
    private function realpath(string $path): string
    {
        $realpath = realpath($path);
        if ($realpath === false) {
            throw new FileNotFoundException(sprintf('The file "%s" was not found.', $path));
        }

        return $realpath;
    }

    /**
     * Returns whether the file path is an absolute path.
     */
    private function isAbsolutePath(string $path): bool
    {
        // Unix filesystem or network path
        if ($path[0] === '/' || $path[0] === '\\') {
            return true;
        }

        // Windows filesystem
        if (strlen($path) > 3 && ctype_alpha($path[0]) && $path[1] === ':' && ($path[2] === '\\' || $path[2] === '/')) {
            return true;
        }

        return false;
    }
}
