<?php
declare(strict_types=1);

namespace Smile\GdprDump;

use ErrorException;
use Smile\GdprDump\Console\Application;
use Smile\GdprDump\Console\Command\DumpCommand;
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
     * Run the application.
     */
    public function run()
    {
        // Convert notices/warnings into exceptions
        $this->initErrorHandler();

        // Build the service container
        $container = $this->buildContainer();

        // Configure and run the application
        $application = new Application();

        /** @var Command $command */
        $command = $container->get(DumpCommand::class);
        $application->add($command);
        $application->setDefaultCommand($command->getName(), true);
        $application->run();
    }

    /**
     * Set the error handler.
     */
    private function initErrorHandler()
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
