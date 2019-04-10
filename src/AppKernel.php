<?php
declare(strict_types=1);

namespace Smile\Anonymizer;

use Smile\Anonymizer\Console\Application;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AppKernel
{
    /**
     * Run the console application.
     *
     * @throws \Exception
     */
    public function run()
    {
        // Initialize the application
        $application = new Application();

        // Initialize the container
        $container = $this->buildContainer($application->getConfigPath());
        $container->setParameter('app_root', APP_ROOT);

        // Add commands to the application
        $commands = array_keys($container->findTaggedServiceIds('console.command'));
        foreach ($commands as $command) {
            if ($container->has($command)) {
                $application->add($container->get($command));
            }
        }

        $application->run();
    }

    /**
     * Build the DI container.
     *
     * @param string $configPath
     * @return ContainerBuilder
     */
    private function buildContainer(string $configPath)
    {
        $container = new ContainerBuilder();
        $loader = new YamlFileLoader($container, new FileLocator($configPath));
        $loader->load('services.yaml');

        return $container;
    }
}
