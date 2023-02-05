<?php

namespace Cyve\JsonDecoder\Tests\Model;

use Cyve\JsonDecoder\JsonDecodableTrait;

class Author
{
    use JsonDecodableTrait;

    public function __construct(
        public string $name,
    ) {
    }
}
