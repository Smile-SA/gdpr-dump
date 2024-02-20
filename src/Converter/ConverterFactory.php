<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use RuntimeException;
use Smile\GdprDump\DependencyInjection\Compiler\ConverterAliasPass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class ConverterFactory
{
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * Create a converter from a name (e.g. "randomizeText").
     */
    public function create(string $name, array $parameters = []): ConverterInterface
    {
        try {
            /** @var ConverterInterface $converter */
            $converter = $this->container->get(ConverterAliasPass::ALIAS_PREFIX . $name);
        } catch (ServiceNotFoundException) {
            throw new RuntimeException(sprintf('The converter "%s" is not defined.', $name));
        }

        $converter->setParameters($parameters);

        return $converter;
    }
}
