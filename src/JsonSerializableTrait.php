<?php

namespace Cyve\JsonDecoder;

trait JsonSerializableTrait
{
    public function jsonSerialize(): mixed
    {
        return (array) clone $this;
    }

    public static function jsonDeserialize(string $json): mixed
    {
        $normalized = json_decode($json, false, 512, JSON_THROW_ON_ERROR);

        if (method_exists(static::class, 'denormalize')) {
            return static::denormalize($normalized);
        }

        return (new Denormalizer())->denormalize($normalized, static::class);
    }
}
