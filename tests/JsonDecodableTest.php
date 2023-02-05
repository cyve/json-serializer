<?php

namespace Cyve\JsonDecoder\Tests;

use Cyve\JsonDecoder\Tests\Model\Author;
use PHPUnit\Framework\TestCase;

class JsonDecodableTest extends TestCase
{
    public function testJsonDecode()
    {
        $author = Author::jsonDecode('{"name":"John Doe"}');

        $this->assertInstanceOf(Author::class, $author);
        $this->assertEquals('John Doe', $author->name);
    }
}
