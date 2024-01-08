<?php

namespace Cyve\JsonDecoder\Tests\Model;

class Author
{
    public function __construct(
        public string $name,
    ) {
    }

    public static function createDummy(): self
    {
        return new self('John Doe');
    }
}
