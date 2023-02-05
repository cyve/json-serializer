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
    private Denormalizer $denormalizer;

    protected function setUp(): void
    {
        $this->denormalizer = new Denormalizer();
    }

    public function testDenormalize()
    {
//        $json = '{"title":"Lorem ipsum","picture":null,"author":{"name":"Cyril"},"creationDate":{"date":"2023-01-27 23:43:13","timezone_type":3,"timezone":"Europe/Paris"},"status":"published","tags":["php"],"views":100,"highlight":true,"picture":null}';
        $input = [
            'title' => 'Lorem ipsum',
            'author' => [
                'name' => 'Cyril',
            ],
            'picture' => 'http://fakeimg.pl/300x300',
            'comments' => [
                ['body' => 'Lorem ipsum'],
                ['body' => 'Sit dolor amet'],
            ],
            'tags' => ['php'],
            'views' => 301,
            'highlight' => true,
            'opengraph' => (object) ['ogTitle' => 'Lorem ipsum'],
            'status' => 'published',
            'creationDate' => [
                'date' => '2000-01-01 00:00:00',
                'timezone_type' => 3,
                'timezone' => 'Europe/Paris',
            ],
            'modificationDate' => '2000-01-02T00:00:00+01:00',
        ];
        $post = $this->denormalizer->denormalize($input, Post::class);
//        dump($post);

        $this->assertEquals('Lorem ipsum', $post->title);
        $this->assertInstanceOf(Author::class, $post->author);
        $this->assertEquals('Cyril', $post->author->name);
        $this->assertEquals('http://fakeimg.pl/300x300', $post->picture);
        $this->assertCount(2, $post->comments);
        $this->assertContainsOnlyInstancesOf(Comment::class, $post->comments);
        $this->assertEquals('Lorem ipsum', $post->comments[0]->body);
        $this->assertEquals('Sit dolor amet', $post->comments[1]->body);
        $this->assertEquals(['php'], $post->tags);
        $this->assertEquals((object) ['ogTitle' => 'Lorem ipsum'], $post->opengraph);
        $this->assertEquals(301, $post->views);
        $this->assertEquals(true, $post->highlight);
        $this->assertEquals(Status::Published, $post->status);
        $this->assertInstanceOf(\DateTime::class, $post->creationDate);
        $this->assertEquals('2000-01-01T00:00:00+0100', $post->creationDate->format(\DateTime::ISO8601));
        $this->assertInstanceOf(\DateTime::class, $post->modificationDate);
        $this->assertEquals('2000-01-02T00:00:00+0100', $post->modificationDate->format(\DateTime::ISO8601));
    }

    public function testDenormalizeWithDefaultValues()
    {
        $post = $this->denormalizer->denormalize(['title' => 'Lorem ipsum', 'author' => ['name' => 'Cyril']], Post::class);

        $this->assertEquals('Lorem ipsum', $post->title);
        $this->assertInstanceOf(Author::class, $post->author);
        $this->assertEquals('Cyril', $post->author->name);
        $this->assertNull($post->picture);
        $this->assertCount(0, $post->comments);
        $this->assertCount(0, $post->tags);
        $this->assertEquals(new \stdClass(), $post->opengraph);
        $this->assertEquals(0, $post->views);
        $this->assertEquals(false, $post->highlight);
        $this->assertEquals(Status::Draft, $post->status);
        $this->assertInstanceOf(\DateTime::class, $post->creationDate);
        $this->assertEquals(time(), $post->creationDate->getTimestamp());
        $this->assertInstanceOf(\DateTime::class, $post->modificationDate);
        $this->assertEquals(time(), $post->modificationDate->getTimestamp());
    }

    public function testGetMetadata()
    {
        $metadata = $this->denormalizer->getMetadata(Post::class);

        $expected = [
            ['title', 'string', false, true, null],
            ['author', Author::class, false, true, null],
            ['picture', 'string', true, false, null],
            ['comments', \ArrayIterator::class, false, false, Comment::class],
            ['tags', 'array', false, false, null],
            ['views', 'int', false, false, null],
            ['highlight', 'bool', false, false, null],
            ['opengraph', 'object', false, false, null],
            ['status', Status::class, false, false, null],
            ['creationDate', \DateTime::class, false, false, null],
            ['modificationDate', \DateTime::class, false, false, null],
        ];

        foreach ($metadata->properties as $i => $property) {
            [$name, $type, $nullable, $required, $collectionOf] = $expected[$i];
            $this->assertEquals($name, $property->name);
            $this->assertEquals($type, $property->type);
            $this->assertEquals($nullable, $property->nullable);
            $this->assertEquals($required, $property->required);
            $this->assertEquals($collectionOf, $property->collectionOf);
        }
    }
}
