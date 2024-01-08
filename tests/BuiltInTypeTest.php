<?php

namespace Cyve\JsonDecoder\Tests;

use Cyve\JsonDecoder\Exception\DenormalizationException;
use Cyve\JsonDecoder\JsonSerializableTrait;
use Cyve\JsonDecoder\Metadata\Metadata;
use PHPUnit\Framework\TestCase;

class BuiltInTypeTest extends TestCase
{
    public function testGetMetadata()
    {
        $metadata = new Metadata(ObjectWithBuiltInTypeProperties::class);

        $expected = [
            ['dateTime', \DateTime::class, false, true, null],
            ['dateTimeImmutable', \DateTimeImmutable::class, false, false, null],
            ['dateTimeZone', \DateTimeZone::class, true, false, null],
            ['dateInterval', \DateInterval::class, true, false, null],
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
        $result = json_encode(new ObjectWithBuiltInTypeProperties(
            dateTime: new \DateTime('1970-01-01 00:00'),
            dateTimeImmutable: new \DateTimeImmutable('1970-01-01 00:00'),
        ));

        $this->assertJsonStringEqualsJsonString('{
            "dateTime": "1970-01-01T00:00:00+01:00",
            "dateTimeImmutable": "1970-01-01T00:00:00+01:00",
            "dateTimeZone": null,
            "dateInterval": null
        }', $result);
    }

    public function testDeserialize()
    {
        $result = ObjectWithBuiltInTypeProperties::jsonDeserialize('{
            "dateTime": "1970-01-01T00:00:00+01:00",
            "dateTimeImmutable": "1970-01-01T00:00:00+01:00",
            "dateTimeZone": "Europe/Paris",
            "dateInterval": "P1D"
        }');

        $this->assertInstanceOf(ObjectWithBuiltInTypeProperties::class, $result);
        $this->assertEquals(new \DateTime('1970-01-01T00:00:00+01:00'), $result->dateTime);
        $this->assertEquals(new \DateTime('1970-01-01T00:00:00+01:00'), $result->dateTimeImmutable);
        $this->assertEquals(new \DateTimeZone('Europe/Paris'), $result->dateTimeZone);
        $this->assertEquals(new \DateInterval('P1D'), $result->dateInterval);
    }

    public function testDeserializeWithInvalidValue()
    {
        $this->expectException(DenormalizationException::class);
        // $this->expectExceptionMessage('state: "foo" is not a valid date');

        ObjectWithBuiltInTypeProperties::jsonDeserialize('{"dateTime": "foo"}');
    }
}

class ObjectWithBuiltInTypeProperties implements \JsonSerializable
{
    use JsonSerializableTrait;

    public function __construct(
        public \DateTime $dateTime,
        public \DateTimeImmutable $dateTimeImmutable = new \DateTimeImmutable(),
        public ?\DateTimeZone $dateTimeZone = null,
        public ?\DateInterval $dateInterval = null,
    ) {
    }
}
