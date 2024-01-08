<?php

namespace Cyve\JsonDecoder\Metadata;

use Cyve\JsonDecoder\Attribute\Collection;

class Metadata
{
    public string $classname;
    public array $properties = [];

    public function __construct(string $classname)
    {
        $this->classname = $classname;

        $reflectionMethod = new \ReflectionMethod($classname, '__construct');
        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $reflectionType = $reflectionParameter->getType();
            $property = [
                'name' => $reflectionParameter->getName(),
                'type' => $reflectionType->getName(),
                'nullable' => $reflectionType->allowsNull() ?? false,
                'required' => false,
                'collectionOf' => null,
            ];

            if ('array' === $reflectionType->getName() || !$reflectionType->isBuiltin() && in_array(\ArrayAccess::class, class_implements($reflectionType->getName()))) {
                if ([] !== $attributes = array_filter($reflectionParameter->getAttributes(), fn ($attribute) => $attribute->getName() === Collection::class)) {
                    $attribute = reset($attributes);
                    $arguments = $attribute->getArguments();
                    $property['collectionOf'] = reset($arguments);
                }
            }

            try {
                $reflectionParameter->getDefaultValue();
            } catch (\ReflectionException) {
                $property['required'] = true;
            }

            $this->properties[] = (object) $property;
        }
    }
}
