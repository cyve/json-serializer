<?php

namespace Cyve\JsonDecoder\Tests;

use Cyve\JsonDecoder\Denormalizer;
use Cyve\JsonDecoder\Tests\Model\Author;
use Cyve\JsonDecoder\Tests\Model\Comment;
use Cyve\JsonDecoder\Tests\Model\Date;
use Cyve\JsonDecoder\Tests\Model\Post;
use Cyve\JsonDecoder\Tests\Model\Status;
use PHPUnit\Framework\TestCase;

class DenormalizerTest extends TestCase
{
    public function testDenormalizePost()
    {
        $input = (object) [
            'title' => 'Lorem ipsum',
            'author' => (object) ['name' => 'John Doe'],
            'picture' => 'http://fakeimg.pl/300x300',
            'comments' => [
                (object) [
                    'body' => 'Lorem ipsum',
                    'author' => (object) ['name' => 'Jane Doe'],
                ],
                (object) [
                    'body' => 'Sit dolor amet',
                    'author' => (object) ['name' => 'Jane Doe'],
                ],
            ],
            'tags' => ['php'],
            'views' => 301,
            'highlight' => true,
            'opengraph' => (object) ['ogTitle' => 'Lorem ipsum'],
            'status' => 'published',
            'creationDate' => '1970-01-01T00:00:00+01:00',
        ];
        $post = (new Denormalizer())->denormalize($input, Post::class);

        $this->assertEquals('Lorem ipsum', $post->title);
        $this->assertEquals(new Author('John Doe'), $post->author);
        $this->assertEquals('http://fakeimg.pl/300x300', $post->picture);
        $this->assertCount(2, $post->comments);
        $this->assertEquals(new Comment('Lorem ipsum', new Author('Jane Doe')), $post->comments[0]);
        $this->assertEquals(new Comment('Sit dolor amet', new Author('Jane Doe')), $post->comments[1]);
        $this->assertEquals(['php'], $post->tags);
        $this->assertEquals((object) ['ogTitle' => 'Lorem ipsum'], $post->opengraph);
        $this->assertEquals(301, $post->views);
        $this->assertEquals(true, $post->highlight);
        $this->assertEquals(Status::Published, $post->status);
        $this->assertEquals(new Date('1970-01-01T00:00:00+01:00'), $post->creationDate);
    }

    public function testDenormalizeDate()
    {
        $result = (new Denormalizer())->denormalize('1970-01-01T00:00:00+01:00', Date::class);

        $this->assertEquals(new Date('1970-01-01T00:00:00+01:00'), $result);
    }

    public function testDenormalizeDateTimeImmutable()
    {
        $result = (new Denormalizer())->denormalize('1970-01-01T00:00:00+01:00', \DateTimeImmutable::class);

        $this->assertEquals(new \DateTimeImmutable('1970-01-01T00:00:00+01:00'), $result);
    }
}
