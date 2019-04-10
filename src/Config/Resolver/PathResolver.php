<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Config\Resolver;

class PathResolver implements PathResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(string $path): string
    {
        $toAbsolutePath = true;

        // Check if it is a config template
        $templates = $this->getTemplates();
        if (array_key_exists($path, $templates)) {
            $path = $templates[$path];
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
                throw new \Exception(sprintf('The file "%s" was not found.', $path));
            }
            $path = $realpath;
        }

        return $path;
    }

    /**
     * Get the config templates.
     *
     * @return string[]
     */
    private function getTemplates(): array
    {
        $templates = [];
        $templatesDirectory = $this->getTemplatesDirectory();

        // Can't use glob, doesn't work with phar
        $files = scandir($templatesDirectory);

        foreach ($files as $fileName) {
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }

            $template = pathinfo($fileName, PATHINFO_FILENAME);
            $templates[$template] = $templatesDirectory . '/' . $fileName;
        }

        return $templates;
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
