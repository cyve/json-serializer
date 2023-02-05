<?php

namespace Cyve\JsonDecoder\Attribute;

#[Attribute(\Attribute::TARGET_PROPERTY)]
class Collection
{
    public function __construct(
        public readonly string $type,
    ) {
    }
}
