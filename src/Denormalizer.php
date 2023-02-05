<?php

namespace Cyve\JsonDecoder;

class Denormalizer
{
    /**
     * TODO:
     * - collections
     * - interfaces
     * - PHP native classes (DateInterval, SplObjectStorage, etc.)
     */
    public function denormalize(array $input, string $classname): mixed
    {
        $metadata = $this->getMetadata($classname);
//        dump($metadata);

        $arguments = [];
        foreach ($metadata->properties as $property) {
            // if no value is provided, then throw error or fallback to default value
            if (!array_key_exists($property->name, $input)) {
                if ($property->required) {
                    throw new \LogicException('Property "'.$property->name.'" is required');
                }
                continue;
            }
            $value = $input[$property->name];

            if (\DateTime::class === $property->type) {
                if (is_string($value)) {
                    $arguments[$property->name] = $property->type::createFromFormat(\DateTime::ISO8601, $value);
                    continue;
                }

                $arguments[$property->name] = new $property->type($value['date'], new \DateTimeZone($value['timezone']));
                continue;
            }

            if (enum_exists($property->type)) {
                $arguments[$property->name] = $property->type::from($value);
                continue;
            }

            if (class_exists($property->type)) {
                $arguments[$property->name] = $this->denormalize($value, $property->type);
                continue;
            }

            $arguments[$property->name] = $value;
        }
//        dump($arguments);

        return new $classname(...$arguments);
    }

    /**
     * TODO:
     * - cache metadata
     */
    public function getMetadata(string $classname): object
    {
        $metadata = (object) ['properties' => []];

        $reflectionMethod = new \ReflectionMethod($classname, '__construct');
        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $reflectionType = $reflectionParameter->getType();
            $property = [
                'name' => $reflectionParameter->getName(),
                'type' => $reflectionType?->getName(),
                'nullable' => $reflectionType?->allowsNull() ?? false,
                'required' => false,
            ];

            try {
                $reflectionParameter->getDefaultValue();
            } catch (\ReflectionException) {
                $property['required'] = true;
            }

            $metadata->properties[] = (object) $property;
        }

        return $metadata;
    }
}
