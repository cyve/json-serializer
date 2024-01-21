<?php

namespace Cyve\JsonDecoder;

use Cyve\JsonDecoder\Exception\DenormalizationException;
use Cyve\JsonDecoder\Metadata\MetadataRegistry;

class Denormalizer
{
    public function __construct(
        private ?MetadataRegistry $metadataRegistry = null,
    ) {
        $this->metadataRegistry ??= new MetadataRegistry();
    }

    /**
     * TODO:
     * - interfaces
     */
    public function denormalize(mixed $normalized, string $type): mixed
    {
        if (!class_exists($type)) {
            throw new \LogicException(sprintf('Class "%s" not found', $type));
        }

        $metadata = $this->metadataRegistry->get($type);

        // use serialized data as first argument for value objects and stringable
        if (is_scalar($normalized)) {
            $firstPropertyName = $metadata->properties[0]->name;
            $normalized = (object) [$firstPropertyName => $normalized];
        }

        $arguments = [];
        foreach ($metadata->properties as $property) {
            try {
                // if no value is provided, then throw error or fallback to default value
                if (!property_exists($normalized, $property->name)) {
                    if ($property->required) {
                        throw new \RuntimeException('undefined value');
                    }
                    continue;
                }

                $value = $normalized->{$property->name};

                // collection
                if (null !== $property->collectionOf) {
                    $arguments[$property->name] = ($property->type === 'array') ? [] : new $property->type();
                    foreach ($value as $key => $element) {
                        $arguments[$property->name][$key] = $this->denormalize($element, $property->collectionOf);
                    }
                    continue;
                }

                // enum
                if (is_subclass_of($property->type, \BackedEnum::class)) {
                    $arguments[$property->name] = $property->type::from($value);
                    continue;
                }

                // object
                if (class_exists($property->type)) {
                    if (method_exists($property->type, 'denormalize')) {
                        $arguments[$property->name] = $property->type::denormalize($normalized);
                    }

                    $arguments[$property->name] = $this->denormalize($value, $property->type);
                    continue;
                }

                // other properties
                $arguments[$property->name] = $value;
            } catch (\Throwable $e) {
                throw new DenormalizationException($property->name, null, $e);
            }
        }

        try {
            return new $type(...$arguments);
        } catch (\TypeError $e) {
            if (preg_match('/Argument #\d \(\$(\w+)\) must be of type (\w+), (\w+) given/', $e->getMessage(), $matches)) {
                throw new DenormalizationException($matches[1], sprintf('invalid type (must be of type %s, %s given)', $matches[2], $matches[3]));
            }

            throw $e;
        }
    }
}
