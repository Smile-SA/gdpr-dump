<?php
declare(strict_types=1);

namespace Smile\GdprDump;

use ErrorException;
use Exception;
use Smile\GdprDump\Console\Application;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @codeCoverageIgnore
 */
class AppKernel
{
    /**
     * Run the console application.
     *
     * @throws Exception
     */
    public function run()
    {
        // Convert notices/warnings into exceptions
        $this->initErrorHandler();

        // Initialize the application
        $application = new Application();

        // Initialize the container
        $basePath = dirname(__DIR__);
        $container = $this->buildContainer($basePath . '/app/config');
        $container->setParameter('app_root', $basePath);

        // Add the main command and run the application
        $application->add($container->get('command.dump'));
        $application->setDefaultCommand('dump', true);
        $application->run();
    }

    /**
     * Build the DI container.
     *
     * @param string $configPath
     * @return ContainerBuilder
     */
    private function buildContainer(string $configPath): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $loader = new YamlFileLoader($container, new FileLocator($configPath));
        $loader->load('services.yaml');

        return $container;
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
}
