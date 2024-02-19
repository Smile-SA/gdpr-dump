<?php

declare(strict_types=1);

namespace Smile\GdprDump\DependencyInjection\Compiler;

use RuntimeException;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConverterAliasPass implements CompilerPassInterface
{
    public const ALIAS_PREFIX = 'converter.';

    /**
     * Add an alias for data converters (e.g. "converter.randomizeText").
     *
     * This allows the converter factory to create converters with the name
     * specified in the config file (e.g. "randomizeText").
     *
     * @throws RuntimeException
     */
    public function process(ContainerBuilder $container): void
    {
        foreach (array_keys($container->findTaggedServiceIds('converter')) as $serviceId) {
            $definition = $container->getDefinition($serviceId);
            $className = $definition->getClass();
            if ($className === null) {
                throw new RuntimeException(
                    sprintf('Failed to find the class name of the service "%s".', $serviceId)
                );
            }

            $aliasName = $this->getAliasName($className);
            if ($container->hasDefinition($aliasName)) {
                throw new RuntimeException(
                    sprintf('The alias "%s" conflicts with an existing service.', $aliasName)
                );
            }

            $alias = new Alias($className, true);
            $container->setAlias($aliasName, $alias);
        }
    }

    /**
     * Get a converter alias name (class name with first letter in lower caps).
     *
     * The alias name contains a prefix to prevent any conflict with other services.
     */
    private function getAliasName(string $className): string
    {
        $parts = explode('\\', $className);

        // Add a prefix to prevent any conflict with other services
        return self::ALIAS_PREFIX . lcfirst(array_pop($parts));
    }
}
