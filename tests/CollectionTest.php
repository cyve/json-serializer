<?php

namespace Cyve\JsonDecoder\Tests;

use Cyve\JsonDecoder\Attribute\Collection;
use Cyve\JsonDecoder\JsonSerializableTrait;
use Cyve\JsonDecoder\Metadata\Metadata;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testGetMetadata()
    {
        $metadata = new Metadata(ObjectWithCollectionProperties::class);

        $expected = [
            ['arrayCollection', 'array', false, false, Foo::class],
            ['arrayIteratorCollection', \ArrayIterator::class, false, false, Bar::class],
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
        $result = json_encode(new ObjectWithCollectionProperties(
            arrayCollection: [
                new Foo('Lorem'),
                new Foo('Ipsum'),
            ],
            arrayIteratorCollection: new \ArrayIterator([
                new Bar('Dolor'),
                new Bar('Amet'),
            ])
        ));

        $this->assertJsonStringEqualsJsonString('{
            "arrayCollection": [
                {"name": "Lorem"},
                {"name": "Ipsum"}
            ],
            "arrayIteratorCollection": [
                {"name": "Dolor"},
                {"name": "Amet"}
            ]
        }', $result);
    }

    public function testDeserialize()
    {
        $result = ObjectWithCollectionProperties::jsonDeserialize('{
            "arrayCollection": [
                {"name": "Lorem"},
                {"name": "Ipsum"}
            ],
            "arrayIteratorCollection": [
                {"name": "Dolor"},
                {"name": "Amet"}
            ]
        }');

        $this->assertInstanceOf(ObjectWithCollectionProperties::class, $result);
        $this->assertCount(2, $result->arrayCollection);
        $this->assertEquals(new Foo('Lorem'), $result->arrayCollection[0]);
        $this->assertEquals(new Foo('Ipsum'), $result->arrayCollection[1]);
        $this->assertCount(2, $result->arrayIteratorCollection);
        $this->assertEquals(new Bar('Dolor'), $result->arrayIteratorCollection[0]);
        $this->assertEquals(new Bar('Amet'), $result->arrayIteratorCollection[1]);
    }

    public function testDeserializeWithDefaultValues()
    {
        $result = ObjectWithCollectionProperties::jsonDeserialize('{}');

        $this->assertInstanceOf(ObjectWithCollectionProperties::class, $result);
        $this->assertEmpty($result->arrayCollection);
    }

    public function testDeserializeOptions()
    {
        $result = ObjectWithOptions::jsonDeserialize('{
            "name": "Lorem ipsum",
            "options": {
                "foo": {
                    "code": "foo",
                    "name": "This is Foo"
                },
                "bar": {
                    "kind": "bar",
                    "name": "This is Bar"
                }
            }
        }');

        $this->assertInstanceOf(ObjectWithOptions::class, $result);
        $this->assertCount(2, $result->options);
        $this->assertEquals(new Foo('This is Foo'), $result->options['foo']);
        $this->assertEquals(new Bar('This is Bar'), $result->options['bar']);
    }
}

class ObjectWithCollectionProperties
{
    use JsonSerializableTrait;

    public function __construct(
        #[Collection(Foo::class)]
        public array $arrayCollection = [],
        #[Collection(Bar::class)]
        public \ArrayIterator $arrayIteratorCollection = new \ArrayIterator([]),
    ) {
    }
}

class ObjectWithOptions
{
    use JsonSerializableTrait { denormalize as denormalizeWithoutOptions; }

    public function __construct(
        public string $name,
        public array $options = [],
    ) {
    }

    public static function denormalize(mixed $data): mixed
    {
        $options = $data->options ?? [];
        unset($data->options);

        $normalized = self::denormalizeWithoutOptions($data);

        foreach ($options as $code => $option) {
            $optionType = match ($code) {
                'foo' => Foo::class,
                'bar' => Bar::class,
            };

            $normalized->options[$code] = $optionType::denormalize($option);
        }

        return $normalized;
    }
}

class Foo
{
    use JsonSerializableTrait;

    public function __construct(
        public string $name,
    ) {
    }
}

class Bar
{
    use JsonSerializableTrait;

    public function __construct(
        public string $name,
    ) {
    }
}
