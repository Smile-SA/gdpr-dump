<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Mapper;

use Smile\GdprDump\Config\ConverterConfig;
use Smile\GdprDump\Config\DumperConfig;
use Smile\GdprDump\Config\Exception\MappingException;
use Smile\GdprDump\Config\FakerConfig;
use Smile\GdprDump\Config\FilterPropagationConfig;
use Smile\GdprDump\Config\TableConfig;
use Smile\GdprDump\Dumper\Config\Definition\Table\SortOrder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

// TODO use in config loader
/**
 * @todo use symfony/object-mapper when the min php version is set to 8.2 or higher (instead of symfony/serializer)
 * @todo use property hooks when the min php version is set to 8.4 or higher (instead of getters/setters)
 */
final class SymfonyMapper/* implements ObjectMapper*/
{
    /**
     * Build a DumperConfig object from the specified domain object.
     */
    public function build(object $data): DumperConfig
    {
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);

        $serializer = new Serializer(
            [
                new ArrayDenormalizer(),
                new GetSetMethodNormalizer($classMetadataFactory, $metadataAwareNameConverter),
            ]
        );

        return $serializer->denormalize(
            $data,
            DumperConfig::class,
            context: $this->getContext($serializer)
        );
    }

    /**
     * Get the context for the denormalization of the main object.
     */
    private function getContext(Serializer $serializer): array
    {
        return [
            AbstractNormalizer::CALLBACKS => [
                'dumpSettings' => function (mixed $value): array {
                    return (array) $value; // stdClass to array
                },
                'connectionParams' => function (mixed $value): array {
                    return (array) $value; // stdClass to array
                },
                'variables' => function (mixed $value): array {
                    return (array) $value; // stdClass to array
                },
                'fakerConfig' => function (mixed $value) use ($serializer): FakerConfig {
                    return $serializer->denormalize($value, FakerConfig::class);
                },
                'filterPropagationConfig' => function (mixed $value) use ($serializer): FilterPropagationConfig {
                    return $serializer->denormalize($value, FilterPropagationConfig::class);
                },
                'tablesConfig' => function (mixed $value) use ($serializer): array {
                    $type = TableConfig::class . '[]';
                    $context = $this->getTablesConfigContext($serializer);

                    return $serializer->denormalize((array) $value, $type, context: $context);
                },
            ],
        ];
    }

    /**
     * Get the context for the denormalization of the tablesConfig object.
     */
    private function getTablesConfigContext(Serializer $serializer): array
    {
        return [
            AbstractNormalizer::CALLBACKS => [
                'sortOrders' => function (mixed $value): array {
                    return $value !== '' ? $this->buildSortOrders($value) : [];
                },
                'convertersConfig' => function (mixed $value) use ($serializer): array {
                    $type = ConverterConfig::class . '[]';
                    $context = $this->getConvertersConfigContext($serializer);
                    $this->removeDisabledContainers($value);

                    return $serializer->denormalize((array) $value, $type, context: $context);
                },
            ],
        ];
    }

    /**
     * Get the context for the denormalization of the convertersConfig object.
     */
    private function getConvertersConfigContext(Serializer $serializer): array
    {
        return [
            AbstractNormalizer::CALLBACKS => [
                'parameters' => function (mixed $value): array {
                    return (array) $value; // stdClass to array
                },
            ],
        ];
    }

    /**
     * Remove disabled containers from the specified object.
     */
    private function removeDisabledContainers(object $convertersData): void
    {
        foreach ((array) $convertersData as $column => $converterData) {
            if (property_exists($converterData, 'disabled') && $converterData['disabled']) {
                unset($convertersData->$column);
            }
        }
    }

    /**
     * Create an array of SortOrder objects from the specified string.
     */
    private function buildSortOrders(string $orderBy): array
    {
        $result = [];
        $orders = explode(',', $orderBy);
        $orders = array_map('trim', $orders);

        foreach ($orders as $order) {
            $parts = explode(' ', $order);

            if (count($parts) > 2) {
                throw new MappingException(sprintf('The sort order "%s" is not valid.', $order));
            }

            $column = $parts[0];
            $direction = $parts[1] ?? SortOrder::DIRECTION_ASC;

            $result[] = new SortOrder($column, $direction);
        }

        return $result;
    }
}
