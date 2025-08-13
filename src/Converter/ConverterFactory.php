<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use Smile\GdprDump\Converter\Exception\ConverterNotFoundException;
use Smile\GdprDump\DependencyInjection\ConverterAliasResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

final class ConverterFactory
{
    public function __construct(
        private ContainerInterface $container,
        private ConverterAliasResolver $converterAliasResolver,
    ) {
    }

    /**
     * Create a converter from a name (e.g. "randomizeText").
     */
    public function create(string $name): Converter
    {
        try {
            $converter = $this->container->get($this->converterAliasResolver->getAliasByName($name));
        } catch (ServiceNotFoundException) {
            throw new ConverterNotFoundException(sprintf('The converter "%s" is not defined.', $name));
        }

        if (!$converter instanceof Converter) {
            throw new ConverterNotFoundException(sprintf('"%s" is not a converter.', $name));
        }

        return $converter;
    }
}
