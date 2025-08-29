<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Loader\Resource;

use Smile\GdprDump\Configuration\Exception\FileNotFoundException;
use Smile\GdprDump\Util\Platform;

final class ResourceLocator
{
    private string $templatesDirectory;

    /**
     * @var string[]
     */
    private ?array $templates = null;

    public function __construct(?string $templatesDirectory = null)
    {
        $this->templatesDirectory = $templatesDirectory ?? dirname(__DIR__, 4) . '/app/config/templates';
    }

    /**
     * Resolves the absolute path of a config template.
     *
     * The $path variable can be a template name (e.g. "magento2") or a relative/absolute path.
     * If the $currentDirectory variable is specified, it is used as the current working directory
     * when resolving relative paths.
     *
     * @throws FileNotFoundException
     */
    public function locate(string $path, ?string $currentDirectory = null): string
    {
        $this->templates ??= $this->getTemplates();

        // Check if it is a config template
        if (array_key_exists($path, $this->templates)) {
            return $this->templates[$path];
        }

        // Absolute path: check if file exists and return the path
        if (Platform::isAbsolutePath($path)) {
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
        if (!is_dir($this->templatesDirectory)) {
            throw new FileNotFoundException(sprintf('The directory "%s" does not exist.', $this->templatesDirectory));
        }

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
}
