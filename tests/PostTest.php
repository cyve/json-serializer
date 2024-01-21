<?php

namespace Cyve\JsonDecoder\Tests;

use Cyve\JsonDecoder\Exception\DenormalizationException;
use Cyve\JsonDecoder\Metadata\Metadata;
use Cyve\JsonDecoder\Tests\Model\Author;
use Cyve\JsonDecoder\Tests\Model\Comment;
use Cyve\JsonDecoder\Tests\Model\Date;
use Cyve\JsonDecoder\Tests\Model\Post;
use Cyve\JsonDecoder\Tests\Model\Status;
use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    public function testGetMetadata()
    {
        $metadata = new Metadata(Post::class);

        $expected = [
            ['title', 'string', false, true, null],
            ['author', Author::class, false, true, null],
            ['picture', 'string', true, false, null],
            ['comments', 'array', false, false, Comment::class],
            ['tags', 'array', false, false, null],
            ['views', 'int', false, false, null],
            ['highlight', 'bool', false, false, null],
            ['opengraph', 'object', false, false, null],
            ['status', Status::class, false, false, null],
            ['creationDate', Date::class, false, false, null],
        ];

        $this->assertCount(count($expected), $metadata->properties);

        foreach ($metadata->properties as $i => $property) {
            [$name, $type, $nullable, $required, $collectionOf] = $expected[$i];
            $this->assertEquals($name, $property->name);
            $this->assertEquals($type, $property->type);
            $this->assertEquals($nullable, $property->nullable);
            $this->assertEquals($required, $property->required);
            $this->assertEquals($collectionOf, $property->collectionOf);
        }
    }

    public function testSerialize()
    {
        $result = json_encode(new Post(
            title: 'Lorem ipsum',
            author: Author::createDummy(),
            picture: 'http://fakeimg.pl/300x300',
            comments: [
                Comment::createDummy(),
                Comment::createDummy(),
            ],
            tags: ['foo', 'bar'],
            views: 301,
            highlight: true,
            opengraph: (object) ['ogTitle' => 'Lorem ipsum'],
            status: Status::Published,
            creationDate: new Date('1970-01-01 00:00'),
        ));

        $this->assertJsonStringEqualsJsonString('{
            "title": "Lorem ipsum",
            "author": {
                "name": "John Doe"
            },
            "picture": "http://fakeimg.pl/300x300",
            "comments": [
                {
                    "body": "Lorem ipsum sit dolor amet",
                    "author": {
                        "name": "John Doe"
                    }
                },
                {
                    "body": "Lorem ipsum sit dolor amet",
                    "author": {
                        "name": "John Doe"
                    }
                }
            ],
            "tags":["foo","bar"],
            "views":301,
            "highlight":true,
            "opengraph":{
                "ogTitle": "Lorem ipsum"
            },
            "status": "published",
            "creationDate": "1970-01-01T00:00:00+01:00"
        }', $result);
    }

    public function testDeserialize()
    {
        $result = Post::jsonDeserialize('{
            "title": "Lorem ipsum",
            "author": {
                "name": "John Doe"
            },
            "picture": "http://fakeimg.pl/300x300",
            "comments": [
                {
                    "body": "Lorem ipsum",
                    "author": {
                        "name": "Jane Doe"
                    }
                },
                {
                    "body": "Sit dolor amet",
                    "author": {
                        "name": "Jane Doe"
                    }
                }
            ],
            "tags":["foo","bar"],
            "views":301,
            "highlight":true,
            "opengraph":{
                "ogTitle": "Lorem ipsum"
            },
            "status": "published",
            "creationDate": "1970-01-01T00:00:00+01:00"
        }');

        $this->assertEquals('Lorem ipsum', $result->title);
        $this->assertEquals(new Author('John Doe'), $result->author);
        $this->assertEquals('http://fakeimg.pl/300x300', $result->picture);
        $this->assertCount(2, $result->comments);
        $this->assertEquals(new Comment('Lorem ipsum', new Author('Jane Doe')), $result->comments[0]);
        $this->assertEquals(new Comment('Sit dolor amet', new Author('Jane Doe')), $result->comments[1]);
        $this->assertEquals(['foo', 'bar'], $result->tags);
        $this->assertEquals(301, $result->views);
        $this->assertEquals(true, $result->highlight);
        $this->assertEquals((object) ['ogTitle' => 'Lorem ipsum'], $result->opengraph);
        $this->assertEquals(Status::Published, $result->status);
        $this->assertEquals(new Date('1970-01-01T00:00:00+01:00'), $result->creationDate);
    }

    public function testDeserializeWithDefaultValues()
    {
        $result = Post::jsonDeserialize('{"title": "Lorem ipsum", "author": {"name": "John Doe"}}');

        $this->assertInstanceOf(Post::class, $result);
        $this->assertEquals('Lorem ipsum', $result->title);
        $this->assertEquals(new Author('John Doe'), $result->author);
        $this->assertEquals(null, $result->picture);
        $this->assertEquals([], $result->tags);
        $this->assertEquals(0, $result->views);
        $this->assertEquals(false, $result->highlight);
        $this->assertEquals(new \stdClass(), $result->opengraph);
        $this->assertEquals(Status::Draft, $result->status);
        $this->assertEquals(date(\DATE_ATOM), $result->creationDate);
    }

    public function testDeserializeWithNullValue()
    {
        $result = Post::jsonDeserialize('{"title": "Lorem ipsum", "author": {"name": "John Doe"}, "picture": null}');

        $this->assertInstanceOf(Post::class, $result);
        $this->assertEquals('Lorem ipsum', $result->title);
        $this->assertEquals(new Author('John Doe'), $result->author);
        $this->assertEquals(null, $result->picture);
    }

    public function testDeserializeWithUndefinedValue()
    {
        $this->expectException(DenormalizationException::class);
        $this->expectExceptionMessage('title: undefined value');

        Post::jsonDeserialize('{}');
    }

    public function testDeserializeWithInvalidType()
    {
        $this->expectException(DenormalizationException::class);
        $this->expectExceptionMessage('title: invalid type (must be of type string, stdClass given');

        Post::jsonDeserialize('{"title": {"foo": "bar"}, "author": {"name": "John Doe"}}');
    }
}
