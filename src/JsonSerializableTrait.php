<?php

namespace Cyve\JsonDecoder;

trait JsonSerializableTrait
{
    public function jsonSerialize(): mixed
    {
        return array_map(function ($value) {
            if ($value instanceof \DateTimeInterface) {
                return $value->format(\DATE_ATOM);
            }

            if ($value instanceof \DateInterval) {
                return $value->format('P%yY%mM%dDT%hH%iM%sS'); // P1Y2M3DT4H5M6S
            }

            return $value;
        }, (array) clone $this);
    }

    public static function jsonDeserialize(string $json): mixed
    {
        return self::denormalize(json_decode($json, false, 512, JSON_THROW_ON_ERROR));
    }

    public static function denormalize(mixed $data): mixed
    {
        return (new Denormalizer())->denormalize($data, static::class);
    }
}
