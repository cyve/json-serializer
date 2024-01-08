<?php

namespace Cyve\JsonDecoder\Tests;

use Cyve\JsonDecoder\Exception\DenormalizationException;
use Cyve\JsonDecoder\JsonSerializableTrait;
use Cyve\JsonDecoder\Metadata\Metadata;
use Cyve\JsonDecoder\Tests\Model\Status;
use PHPUnit\Framework\TestCase;

class BasicTypeTest extends TestCase
{
    public function testGetMetadata()
    {
        $metadata = new Metadata(ObjectWithBasicTypeProperties::class);

        $expected = [
            ['name', 'string', false, true, null],
            ['picture', 'string', true, false, null],
            ['tags', 'array', false, false, null],
            ['stock', 'int', false, false, null],
            ['highlight', 'bool', false, false, null],
            ['opengraph', 'object', false, false, null],
            ['status', Status::class, false, false, null],
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
        $ObjectWithBasicTypeProperties = new ObjectWithBasicTypeProperties(
            name: 'Lorem ipsum',
            picture: 'https://fakeimg.pl/300x300',
            tags: ['lorem', 'ipsum'],
            stock: 10,
            highlight: true,
            opengraph: (object) ['title' => 'Lorem ipsum', 'url' => 'https://example.com/lorem-ipsum'],
            status: Status::Published,
        );
        $result = json_encode($ObjectWithBasicTypeProperties);

        $this->assertJsonStringEqualsJsonString('{
            "name": "Lorem ipsum",
            "picture": "https://fakeimg.pl/300x300",
            "tags": ["lorem", "ipsum"],
            "stock": 10,
            "highlight": true,
            "opengraph": {
                "title": "Lorem ipsum",
                "url": "https://example.com/lorem-ipsum"
            },
            "status": "published"
        }', $result);
    }

    public function testDeserialize()
    {
        $result = ObjectWithBasicTypeProperties::jsonDeserialize('{
            "name": "Lorem ipsum",
            "picture": "https://fakeimg.pl/300x300",
            "tags": ["lorem", "ipsum"],
            "stock": 10,
            "highlight": true,
            "opengraph": {
                "title": "Lorem ipsum",
                "url": "https://example.com/lorem-ipsum"
            },
            "status": "published"
        }');

        $this->assertInstanceOf(ObjectWithBasicTypeProperties::class, $result);
        $this->assertEquals('Lorem ipsum', $result->name);
        $this->assertEquals('https://fakeimg.pl/300x300', $result->picture);
        $this->assertEquals(['lorem', 'ipsum'], $result->tags);
        $this->assertEquals(10, $result->stock);
        $this->assertEquals(true, $result->highlight);
        $this->assertEquals((object) ['title' => 'Lorem ipsum', 'url' => 'https://example.com/lorem-ipsum'], $result->opengraph);
        $this->assertEquals(Status::Published, $result->status);
    }

    public function testDeserializeWithDefaultValues()
    {
        $result = ObjectWithBasicTypeProperties::jsonDeserialize('{"name": "Lorem ipsum"}');

        $this->assertInstanceOf(ObjectWithBasicTypeProperties::class, $result);
        $this->assertEquals('Lorem ipsum', $result->name);
        $this->assertEquals(null, $result->picture);
        $this->assertEquals([], $result->tags);
        $this->assertEquals(0, $result->stock);
        $this->assertEquals(false, $result->highlight);
        $this->assertEquals(new \stdClass(), $result->opengraph);
        $this->assertEquals(Status::Draft, $result->status);
    }

    public function testDeserializeWithNullValue()
    {
        $result = ObjectWithBasicTypeProperties::jsonDeserialize('{"name": "Lorem ipsum", "picture": null}');

        $this->assertInstanceOf(ObjectWithBasicTypeProperties::class, $result);
        $this->assertEquals('Lorem ipsum', $result->name);
        $this->assertEquals(null, $result->picture);
    }

    public function testDeserializeWithUndefinedValue()
    {
        $this->expectException(DenormalizationException::class);
        $this->expectExceptionMessage('name: undefined value');

        ObjectWithBasicTypeProperties::jsonDeserialize('{}');
    }

    public function testDeserializeWithInvalidType()
    {
        $this->expectException(DenormalizationException::class);
        $this->expectExceptionMessage('name: invalid type (must be of type string, stdClass given');

        ObjectWithBasicTypeProperties::jsonDeserialize('{"name": {"foo": "bar"}}');
    }
}

class ObjectWithBasicTypeProperties
{
    use JsonSerializableTrait;

    public function __construct(
        public string $name,
        public ?string $picture = null,
        public array $tags = [],
        public int $stock = 0,
        public bool $highlight = false,
        public object $opengraph = new \stdClass(),
        public Status $status = Status::Draft,
    ) {
    }
}
