<?php

namespace Cyve\JsonDecoder\Attribute;

#[Attribute]
class Collection
{
    public function __construct(
        public readonly string $type,
    ) {
    }
}
