<?php
declare(strict_types=1);

namespace Smile\GdprDump;

use CachedContainer;
use ErrorException;
use Smile\GdprDump\Console\Application;
use Smile\GdprDump\Console\Command\DumpCommand;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
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
     * @var bool
     */
    private $debug = false;

    /**
     * Initialize the application.
     */
    public function boot()
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
     * Run the application.
     */
    public function run()
    {
        // Initialize the application dependencies
        $this->boot();

        // Configure and run the application
        $application = new Application();

        /** @var Command $command */
        $command = $this->container->get(DumpCommand::class);
        $application->add($command);
        $application->setDefaultCommand($command->getName(), true);
        $application->run();
    }

    /**
     * Toggle the debug mode.
     *
     * @param bool $value
     */
    public function setDebug(bool $value)
    {
        $this->debug = $value;
    }

    /**
     * Check whether debug mode is enabled.
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
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

        // Initialize the config cache
        $file = $basePath . '/app/container.php';
        $configCache = new ConfigCache($file, $this->isDebug());

        // Load the container from the cache if it exists
        if ($configCache->isFresh()) {
            require_once $file;
            return new CachedContainer();
        }

        // Build the container
        $container = new ContainerBuilder();
        $loader = new YamlFileLoader($container, new FileLocator($basePath . '/app/config'));
        $loader->load('services.yaml');
        $container->setParameter('app_root', $basePath);
        $container->compile();

        // Dump the container contents to the cache
        $dumper = new PhpDumper($container);
        $configCache->write($dumper->dump(['class' => 'CachedContainer']));

        return $container;
    }
}
