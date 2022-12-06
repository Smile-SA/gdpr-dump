<?php

declare(strict_types=1);

namespace Smile\GdprDump;

use ErrorException;
use RuntimeException;
use Smile\GdprDump\Console\Application;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AppKernel
{
    private ContainerInterface $container;
    private bool $booted = false;

    /**
     * Run the application.
     *
     * The console command is not lazy-loaded (cf. https://symfony.com/doc/6.2/console/lazy_commands.html)
     * because this feature is not useful in a single command application.
     */
    public function run(string $command = 'command.dump'): void
    {
        $this->boot();
        $application = new Application();

        /** @var Command $defaultCommand */
        $defaultCommand = $this->container->get($command);
        $application->add($defaultCommand);
        $application->setDefaultCommand((string) $defaultCommand->getName(), true);
        $application->run();
    }

    /**
     * Boot the kernel.
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        // Convert notices/warnings into exceptions
        $this->initErrorHandler();

        // Build the service container
        $this->container = $this->buildContainer();

        $this->booted = true;
    }

    /**
     * Get the container.
     *
     * @throws RuntimeException
     */
    public function getContainer(): ContainerInterface
    {
        if (!$this->booted) {
            throw new RuntimeException('The kernel is not initialized.');
        }

        return $this->container;
    }

    /**
     * Set the error handler.
     */
    private function initErrorHandler(): void
    {
        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            // Error was suppressed with the "@" operator
            if (error_reporting() === 0) {
                return false;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });
    }

    /**
     * Build the service container.
     *
     * The container is not cached (cf. https://symfony.com/doc/6.2/components/dependency_injection/compilation.html#dumping-the-configuration-for-performance)
     * because the cache file would contain hardcoded paths (e.g. app_root).
     * It would prevent the phar file from working.
     * As a consequence, for performance reasons, autowiring is also disabled.
     */
    private function buildContainer(): ContainerInterface
    {
        $basePath = dirname(__DIR__);
        $container = new ContainerBuilder();

        $loader = new YamlFileLoader($container, new FileLocator($basePath . '/app/config'));
        $loader->load('services.yaml');

        $container->setParameter('app_root', $basePath);
        $container->compile();

        return $container;
    }
}
