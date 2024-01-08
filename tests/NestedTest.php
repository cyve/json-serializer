<?php

namespace Cyve\JsonDecoder\Tests;

use Cyve\JsonDecoder\Exception\DenormalizationException;
use Cyve\JsonDecoder\JsonSerializableTrait;
use Cyve\JsonDecoder\Metadata\Metadata;
use PHPUnit\Framework\TestCase;

class NestedTest extends TestCase
{
    public function testGetMetadata()
    {
        $metadata = new Metadata(ObjectWithNestedProperty::class);

        $expected = [
            ['nested', Nested::class, false, true, null],
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
        $result = json_encode(new ObjectWithNestedProperty(new Nested('Lorem ipsum')));

        $this->assertJsonStringEqualsJsonString('{
            "nested": {
                "name": "Lorem ipsum"
            }
        }', $result);
    }

    public function testDeserialize()
    {
        $result = ObjectWithNestedProperty::jsonDeserialize('{
            "nested": {
                "name": "Lorem ipsum"
            }
        }');

        $this->assertInstanceOf(ObjectWithNestedProperty::class, $result);
        $this->assertEquals(new Nested('Lorem ipsum'), $result->nested);
    }

    public function testDeserializeWithInvalidValue()
    {
        $this->expectException(DenormalizationException::class);
        // $this->expectExceptionMessage('state: "Lorem ipsum" is not a valid backing value for enum');

        ObjectWithNestedProperty::jsonDeserialize('{"nested": 5}');
    }

    public function testDeserializeWithInvalidNestedValue()
    {
        $this->expectException(DenormalizationException::class);
        $this->expectExceptionMessage('nested.name: invalid type (must be of type string, stdClass given)');

        ObjectWithNestedProperty::jsonDeserialize('{"nested": {"name": {"foo": "bar"}}}');
    }
}

class ObjectWithNestedProperty
{
    use JsonSerializableTrait;

    public function __construct(
        public Nested $nested,
    ) {
    }
}

class Nested
{
    public function __construct(
        public string $name,
    ) {
    }
}
