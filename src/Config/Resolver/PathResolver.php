<?php
declare(strict_types=1);

namespace Smile\GdprDump\Config\Resolver;

class PathResolver implements PathResolverInterface
{
    /**
     * @var array
     */
    private $templates;

    /**
     * @inheritdoc
     */
    public function resolve(string $path, string $currentPath = null): string
    {
        $isTemplate = $this->isTemplate($path);

        // Check if it is a config template
        if ($isTemplate) {
            $path = $this->getTemplate($path);
            return $this->realpath($path);
        }

        // Handle "~" character
        if (function_exists('posix_getuid') && strpos($path, '~') !== false) {
            $info = posix_getpwuid(posix_getuid());
            $path = str_replace('~', $info['dir'], $path);
        }

        // Absolute path: check if file exists and return the path
        if ($this->isAbsolutePath($path)) {
            if (!file_exists($path)) {
                throw new FileNotFoundException(sprintf('The file "%s" was not found.', $path));
            }

            return $path;
        }

        // Append the current path if specified
        if ($currentPath !== null) {
            $path = $currentPath . '/' . $path;
        }

        // Get the absolute path (to ensure compatibility with phar file)
        return $this->realpath($path);
    }

    /**
     * Check if the specified file is a config template.
     *
     * @param string $name
     * @return bool
     */
    private function isTemplate(string $name): bool
    {
        $templates = $this->getTemplates();

        return array_key_exists($name, $templates);
    }

    /**
     * Get the path to a template.
     *
     * @param string $name
     * @return string
     */
    private function getTemplate(string $name): string
    {
        return $this->getTemplates()[$name];
    }

    /**
     * Get the config templates.
     *
     * @return string[]
     */
    private function getTemplates(): array
    {
        if ($this->templates !== null) {
            return $this->templates;
        }

        $templatesDirectory = $this->getTemplatesDirectory();

        // Can't use glob, doesn't work with phar
        $files = scandir($templatesDirectory);

        foreach ($files as $fileName) {
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }

            $template = pathinfo($fileName, PATHINFO_FILENAME);
            $this->templates[$template] = $templatesDirectory . '/' . $fileName;
        }

        return $this->templates;
    }

    /**
     * Get the templates directory.
     *
     * @return string
     */
    private function getTemplatesDirectory(): string
    {
        return APP_ROOT . '/config/templates';
    }

    /**
     * Get the absolute path of a file.
     *
     * @param string $path
     * @return string
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
     *
     * @param string $path
     * @return bool
     */
    private function isAbsolutePath(string $path): bool
    {
        if ($path[0] === '/' || $path[0] === '\\'
            || (strlen($path) > 3 && ctype_alpha($path[0])
                && $path[1] === ':'
                && ($path[2] === '\\' || $path[2] === '/')
            )
            || parse_url($path, PHP_URL_SCHEME) !== null
        ) {
            return true;
        }

        return false;
    }
}
