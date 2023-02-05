<?php

namespace Cyve\JsonDecoder\Tests\Model;

use Cyve\JsonDecoder\JsonDecodableTrait;

class Comment
{
    use JsonDecodableTrait;

    public function __construct(
        public string $body,
    ) {
    }
}
