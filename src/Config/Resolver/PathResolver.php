<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Config\Resolver;

class PathResolver implements PathResolverInterface
{
    /**
     * @var array
     */
    private $templates;

    /**
     * @inheritdoc
     */
    public function resolve(string $path): string
    {
        $toAbsolutePath = true;

        // Check if it is a config template
        if ($this->isTemplate($path)) {
            $path = $this->getTemplate($path);
            $toAbsolutePath = false;
        }

        // Handle "~" character
        if (function_exists('posix_getuid') && strpos($path, '~') !== false) {
            $info = posix_getpwuid(posix_getuid());
            $path = str_replace('~', $info['dir'], $path);
        }

        // Compatibility with phar files: relative paths must be resolved with realpath
        if ($toAbsolutePath) {
            $realpath = realpath($path);
            if ($realpath === false) {
                throw new \RuntimeException(sprintf('The file "%s" was not found.', $path));
            }
            $path = $realpath;
        }

        return $path;
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
}
