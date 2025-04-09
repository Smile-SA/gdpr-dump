<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use RuntimeException;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\DependencyInjection\ConverterAliasResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

final class ConverterFactory
{
    public function __construct(
        private ContainerInterface $container,
        private ConverterAliasResolver $converterAliasResolver
    ) {
    }

    /**
     * Create a converter from a name (e.g. "randomizeText").
     */
    public function create(string $name, array $parameters = []): ConverterInterface
    {
        try {
            /** @var ConverterInterface $converter */
            $converter = $this->container->get($this->converterAliasResolver->getAliasByName($name));
        } catch (ServiceNotFoundException) {
            throw new RuntimeException(sprintf('The converter "%s" is not defined.', $name));
        }

        try {
            $converter->setParameters($parameters);
        } catch (ValidationException $e) {
            throw new RuntimeException(
                sprintf('An error occurred while parsing the converter "%s": %s', $name, lcfirst($e->getMessage()))
            );
        }

        return $converter;
    }
}
