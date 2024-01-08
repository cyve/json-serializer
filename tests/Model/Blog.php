<?php

namespace Cyve\JsonDecoder\Tests\Model;

use Cyve\JsonDecoder\Attribute\Collection;
use Cyve\JsonDecoder\JsonSerializableTrait;

class Blog implements \JsonSerializable
{
    use JsonSerializableTrait;

    public function __construct(
        #[Collection(Post::class)]
        public array $posts = [],
    ) {
    }

    public static function createDummy(): self
    {
        return new self(array_map(fn () => Post::createDummy(), range(1, 50)));
    }
}