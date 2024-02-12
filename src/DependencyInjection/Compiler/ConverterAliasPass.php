<?php

declare(strict_types=1);

namespace Smile\GdprDump\DependencyInjection\Compiler;

use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConverterAliasPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container): void
    {
        foreach (array_keys($container->findTaggedServiceIds('converter')) as $serviceId) {
            $definition = $container->getDefinition($serviceId);
            $name = $this->getConverterAlias($definition);
            $container->setDefinition($name, $definition);
        }
    }

    /**
     * Get converter alias (class name with first letter in lower caps).
     *
     * @throws RuntimeException
     */
    private function getConverterAlias(Definition $definition): string
    {
        $className = $definition->getClass();
        if ($className === null) {
            throw new RuntimeException('Invalid service definition.');
        }

        $parts = explode('\\', $className);

        return lcfirst(array_pop($parts));
    }
}
