<?php

namespace Cyve\JsonDecoder\Metadata;

class MetadataRegistry
{
    private static $metadata = [];

    public function get(string $classname): Metadata
    {
        return self::$metadata[$classname] ??= new Metadata($classname);
    }
}
