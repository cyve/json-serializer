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
     * - PHP native classes (DateInterval, SplObjectStorage, etc.)
     */
    public function denormalize(mixed $input, string $type): mixed
    {
        // scalar or object
        $scalarTypes = ['int' => 'int', 'float' => 'float', 'string' => 'string', 'bool' => 'bool'];
        if (array_key_exists($type, $scalarTypes) || 'object' === $type) {
            return $input;
        }

        // enum
        if (is_subclass_of($type, \BackedEnum::class)) {
            return $type::from($input);
        }

        // DateTime
        if (is_subclass_of($type, \DateTimeInterface::class) && is_string($input)) {
            return new $type($input);
        }

        // DateTimeZone
        if ($type === \DateTimeZone::class && is_string($input)) {
            return new \DateTimeZone($input);
        }

        // DateInterval
        if ($type === \DateInterval::class && is_string($input)) {
            return new \DateInterval($input);
        }

        // array of scalars
        if ('array' === $type && array_is_list($input) && array_key_exists(gettype($input[0]), $scalarTypes)) {
            return $input;
        }

        // other types
        if (!class_exists($type)) {
            throw new \RuntimeException(sprintf('Unsupported type "%s"', $type));
        }

        $metadata = $this->metadataRegistry->get($type);

        $arguments = [];
        foreach ($metadata->properties as $property) {
            try {
                // if no value is provided, then throw error or fallback to default value
                if (!property_exists($input, $property->name)) {
                    if ($property->required) {
                        throw new \RuntimeException('undefined value');
                    }
                    continue;
                }

                $value = $input->{$property->name};

                // if the property is a collection
                if (null !== $property->collectionOf) {
                    $arguments[$property->name] = ($property->type === 'array') ? [] : new $property->type();
                    foreach ($value as $key => $element) {
                        $arguments[$property->name][$key] = $this->denormalize($element, $property->collectionOf);
                    }
                    continue;
                }

                // if the property class overwrite JsonDecodableTrait::denormalize();
                if (class_exists($property->type)
                    && array_key_exists(JsonSerializableTrait::class, class_uses($property->type))
                    && method_exists($property->type, 'denormalize')) {
                    $arguments[$property->name] = $property->type::denormalize($value);
                    continue;
                }

                // else for other properties
                $arguments[$property->name] = $this->denormalize($value, $property->type);
            } catch (\Throwable $e) {
                throw new DenormalizationException($property->name, null, $e);
            }
        }
        // dump($arguments);

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
