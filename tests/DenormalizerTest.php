<?php

namespace Cyve\JsonDecoder\Tests;

use Cyve\JsonDecoder\Denormalizer;
use Cyve\JsonDecoder\Tests\Model\Author;
use Cyve\JsonDecoder\Tests\Model\Comment;
use Cyve\JsonDecoder\Tests\Model\Post;
use Cyve\JsonDecoder\Tests\Model\Status;
use PHPUnit\Framework\TestCase;

class DenormalizerTest extends TestCase
{
    /**
     * @testWith [1, "int"]
     *           [1.1, "float"]
     *           ["foo", "string"]
     *           [true, "bool"]
     */
    public function testDenormalizeScalar($value, $type)
    {
        $this->assertEquals($value, (new Denormalizer())->denormalize($value, $type));
    }

    /**
     * @testWith [{"foo": "bar"}, "object"]
     *           [["foo", "bar"], "array"]
     */
    public function testDenormalizeComposite($value, $type)
    {
        $this->assertEquals($value, (new Denormalizer())->denormalize($value, $type));
    }

    public function testDenormalizeEnum()
    {
        $this->assertEquals(Status::Draft, (new Denormalizer())->denormalize('draft', Status::class));
    }

    public function testDenormalizeDateTime()
    {
        $result = (new Denormalizer())->denormalize('1970-01-01T00:00:00+01:00', \DateTime::class);

        $this->assertEquals(new \DateTime('1970-01-01T00:00:00+01:00'), $result);
    }

    public function testDenormalizeDateTimeImmutable()
    {
        $result = (new Denormalizer())->denormalize('1970-01-01T00:00:00+01:00', \DateTimeImmutable::class);

        $this->assertEquals(new \DateTimeImmutable('1970-01-01T00:00:00+01:00'), $result);
    }

    public function testDenormalizeDateTimeZone()
    {
        $result = (new Denormalizer())->denormalize('Europe/Paris', \DateTimeZone::class);

        $this->assertEquals(new \DateTimeZone('Europe/Paris'), $result);
    }

    public function testDenormalizeDateInterval()
    {
        $result = (new Denormalizer())->denormalize('P1D', \DateInterval::class);

        $this->assertEquals(new \DateInterval('P1D'), $result);
    }

    public function testDenormalizeObject()
    {
        $result = (new Denormalizer())->denormalize((object) ['name' => 'John Doe'], Author::class);

        $this->assertEquals(new Author('John Doe'), $result);
    }

    public function testDenormalizeAggregate()
    {
        $input = (object) [
            'title' => 'Lorem ipsum',
            'author' => (object) ['name' => 'John Doe'],
            'picture' => 'http://fakeimg.pl/300x300',
            'comments' => [
                (object) [
                    'body' => 'Lorem ipsum',
                    'author' => (object) ['name' => 'Jane Doe'],
                    'creationDate' => '2000-01-01T00:00:00+01:00',
                ],
                (object) [
                    'body' => 'Sit dolor amet',
                    'author' => (object) ['name' => 'Jane Doe'],
                    'creationDate' => '2000-01-01T00:00:00+01:00',
                ],
            ],
            'tags' => ['php'],
            'views' => 301,
            'highlight' => true,
            'opengraph' => (object) ['ogTitle' => 'Lorem ipsum'],
            'status' => 'published',
            'creationDate' => '2000-01-01T00:00:00+01:00',
            'modificationDate' => '2000-01-02T00:00:00+01:00',
        ];
        $post = (new Denormalizer())->denormalize($input, Post::class);
//        dump($post);

        $this->assertEquals('Lorem ipsum', $post->title);
        $this->assertEquals(new Author('John Doe'), $post->author);
        $this->assertEquals('http://fakeimg.pl/300x300', $post->picture);
        $this->assertCount(2, $post->comments);
        $this->assertEquals(new Comment('Lorem ipsum', new Author('Jane Doe'), new \DateTime('2000-01-01T00:00:00+01:00')), $post->comments[0]);
        $this->assertEquals(new Comment('Sit dolor amet', new Author('Jane Doe'), new \DateTime('2000-01-01T00:00:00+01:00')), $post->comments[1]);
        $this->assertEquals(['php'], $post->tags);
        $this->assertEquals((object) ['ogTitle' => 'Lorem ipsum'], $post->opengraph);
        $this->assertEquals(301, $post->views);
        $this->assertEquals(true, $post->highlight);
        $this->assertEquals(Status::Published, $post->status);
        $this->assertEquals(new \DateTime('2000-01-01T00:00:00+01:00'), $post->creationDate);
        $this->assertEquals(new \DateTime('2000-01-02T00:00:00+01:00'), $post->modificationDate);
    }
}
