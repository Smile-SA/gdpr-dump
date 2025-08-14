<?php

declare(strict_types=1);

namespace Smile\GdprDump;

use ErrorException;
use Exception;
use GdprDumpCachedContainer;
use RuntimeException;
use Smile\GdprDump\Console\Application;
use Smile\GdprDump\Console\Command\DumpCommand;
use Smile\GdprDump\DependencyInjection\Compiler\ConverterAliasPass;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Exception\IOException;

final class Kernel
{
    private ContainerInterface $container;
    private bool $booted = false;
    private bool $debug = false;

    /**
     * Run the application.
     *
     * The console command is not lazy-loaded (cf. https://symfony.com/doc/6.2/console/lazy_commands.html)
     * because this feature is not useful in a single command application.
     *
     * @throws Exception
     */
    public function run(string $command = DumpCommand::class): void
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
     *
     * @throws Exception
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        // Convert notices/warnings into exceptions
        $this->initErrorHandler();

        // Load the .env file if it exists
        $this->bootEnv();

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
     *
     * @throws ErrorException
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
     * Load the .env file if it exists.
     */
    private function bootEnv(): void
    {
        $env = dirname(__DIR__) . '/.env';

        if (is_file($env)) {
            (new Dotenv())->load($env);

            // Detect debug mode (always false when run from the phar file, because the .env file is not included)
            // phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
            $this->debug = (bool) ($_ENV['APP_DEBUG'] ?? false);
        }
    }

    /**
     * Fetch the service container from a cache file, or create it if the file doesn't exist.
     */
    private function buildContainer(): ContainerInterface
    {
        $file = dirname(__DIR__) . '/var/container_cache.php';
        $containerConfigCache = new ConfigCache($file, $this->debug);

        if (!$containerConfigCache->isFresh()) {
            $container = $this->createContainer();

            // Dump the container to the cache file
            $dumper = new PhpDumper($container);
            /** @var string $content */
            $content = $dumper->dump(['class' => 'GdprDumpCachedContainer']);

            try {
                $containerConfigCache->write($content, $container->getResources());
            } catch (IOException) {
                // Don't prevent the application from running if the file creation failed
                return $container;
            }
        }

        // Fetch the container from the cache
        require_once $file;

        // @phpstan-ignore-next-line
        return new GdprDumpCachedContainer();
    }

    /**
     * Create the service container.
     */
    private function createContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/app/config'));
        $loader->load('services.yaml');

        $container->addCompilerPass(new RegisterListenersPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new ConverterAliasPass());

        $container->register('event_dispatcher', EventDispatcher::class);
        $container->setAlias(EventDispatcherInterface::class, 'event_dispatcher');
        $container->setAlias(ContainerInterface::class, 'service_container'); // used by ConverterFactory
        $container->compile();

        return $container;
    }
}
