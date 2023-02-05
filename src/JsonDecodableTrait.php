<?php

namespace Cyve\JsonDecoder;

trait JsonDecodableTrait
{
    public static function jsonDecode(string $json): mixed
    {
        return (new Denormalizer())->denormalize(
            json_decode($json, true, 512, JSON_THROW_ON_ERROR),
            static::class,
        );
    }
}
