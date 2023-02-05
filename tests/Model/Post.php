<?php

namespace Cyve\JsonDecoder\Tests\Model;

use Cyve\JsonDecoder\JsonDecodableTrait;

class Post
{
    use JsonDecodableTrait;

    public function __construct(
        public string $title,
        public Author $author,
        public ?string $picture = null,
//        #[Collection(Comment::class)]
//        public array $comments = [],
        public array $tags = [],
        public int $views = 0,
        public bool $highlight = false,
        public object $opengraph = new \stdClass(),
        public Status $status = Status::Draft,
        public \DateTime $creationDate = new \DateTime(),
        public \DateTime $modificationDate = new \DateTime(),
    ) {}
}
