<?php

namespace Cyve\JsonDecoder\Tests\Model;

use Cyve\JsonDecoder\Attribute\Collection;
use Cyve\JsonDecoder\JsonSerializableTrait;

class Post implements \JsonSerializable
{
    use JsonSerializableTrait;

    public function __construct(
        public string $title,
        public Author $author,
        public ?string $picture = null,
        #[Collection(Comment::class)]
        public array $comments = [],
        public array $tags = [],
        public int $views = 0,
        public bool $highlight = false,
        public object $opengraph = new \stdClass(),
        public Status $status = Status::Draft,
        public \DateTimeImmutable $creationDate = new \DateTimeImmutable(),
        public \DateTime $modificationDate = new \DateTime(),
    ) {
    }

    public static function createDummy(): self
    {
        return new self(
            title: 'Lorem ipsum',
            author: Author::createDummy(),
            picture: 'http://fakeimg.pl/300x300',
            comments: array_map(fn () => Comment::createDummy(), range(1, 10)),
            tags: ['foo', 'bar'],
            views: 301,
            highlight: true,
            opengraph: (object) ['ogTitle' => 'Lorem ipsum'],
            status: Status::Published,
            creationDate: new \DateTimeImmutable('1970-01-01 00:00'),
            modificationDate: new \DateTime('1970-01-01 00:00')
        );
    }
}
