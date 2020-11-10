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

/**
 * @codeCoverageIgnore
 */
class AppKernel
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var bool
     */
    private $booted = false;

    /**
     * Run the application.
     */
    public function run(): void
    {
        $this->boot();
        $application = new Application();

        /** @var Command $command */
        $command = $this->container->get('command.dump');
        $application->add($command);
        $application->setDefaultCommand($command->getName(), true);
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
     * @return ContainerInterface
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
            if (0 === error_reporting()) {
                return false;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });
    }

    /**
     * Build the service container.
     *
     * @return ContainerInterface
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
