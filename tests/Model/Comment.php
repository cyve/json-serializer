<?php

namespace Cyve\JsonDecoder\Tests\Model;

class Comment
{
    public function __construct(
        public string $body,
        public Author $author,
    ) {
    }

    public static function createDummy(): self
    {
        return new self(
            body: 'Lorem ipsum sit dolor amet',
            author: Author::createDummy(),
        );
    }
}
