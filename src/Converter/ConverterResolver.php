<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use ReflectionClass;
use ReflectionException;
use RuntimeException;

class ConverterResolver
{
    private array $pathsByNamespace = [];

    /**
     * e.g. ['unique' => 'Smile\GdprDump\Converter\Proxy\Unique', ...]
     */
    private ?array $resolved = null;

    /**
     * Constructor. Default converters are automatically included.
     */
    public function __construct()
    {
        $this->addPath('Smile\\GdprDump\\Converter\\', __DIR__);
    }

    /**
     * Add a path.
     *
     * @param string $namespace
     * @param string $path
     * @return $this
     */
    public function addPath(string $namespace, string $path): self
    {
        $this->pathsByNamespace[$namespace][] = $path;

        return $this;
    }

    /**
     * Get a converter class name by converter name.
     *
     * @param string $name
     * @return string
     * @throws RuntimeException
     */
    public function getClassName(string $name): string
    {
        if (strpos($name, '\\') !== false) {
            return $name;
        }

        try {
            $this->resolveClassNames();
        } catch (ReflectionException $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        if (!array_key_exists($name, $this->resolved)) {
            throw new RuntimeException(sprintf('The converter "%s" is not defined.', $name));
        }

        return $this->resolved[$name];
    }

    /**
     * Initialize the converter name <-> class name array.
     *
     * @throws ReflectionException
     */
    private function resolveClassNames(): void
    {
        if ($this->resolved !== null) {
            return;
        }

        $this->resolved = [];
        foreach ($this->pathsByNamespace as $namespace => $paths) {
            foreach ($paths as $path) {
                $this->resolved = array_merge($this->resolved, $this->findClassNames($namespace, $path));
            }
        }
    }

    /**
     * Find converter class names that reside in the specified directory.
     * e.g. ['unique' => 'Smile\GdprDump\Data\Converter\Proxy\Unique', ...]
     *
     * @param string $namespace
     * @param string $directory
     * @param string $baseDirectory
     * @return array
     * @throws ReflectionException
     */
    private function findClassNames(string $namespace, string $directory, string $baseDirectory = ''): array
    {
        $result = [];
        $files = scandir($directory);

        foreach ($files as $fileName) {
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }

            // Absolute path of the file
            $path = $directory . '/' . $fileName;

            if (is_dir($path)) {
                // Recursively find files in this directory
                $newBaseDirectory = $baseDirectory !== '' ? $baseDirectory . '/' . $fileName : $fileName;
                $result = array_merge($result, $this->findClassNames($namespace, $path, $newBaseDirectory));
            } else {
                // Remove the extension
                $fileName = pathinfo($fileName, PATHINFO_FILENAME);

                // Get the class name
                $className = $namespace;
                $className .= $baseDirectory !== ''
                    ? str_replace('/', '\\', $baseDirectory) . '\\' . $fileName
                    : $fileName;

                // Include only classes that implement the converter interface
                $reflection = new ReflectionClass($className);

                if ($reflection->isSubclassOf(ConverterInterface::class)) {
                    $result[lcfirst($fileName)] = $className;
                }
            }
        }

        return $result;
    }
}
