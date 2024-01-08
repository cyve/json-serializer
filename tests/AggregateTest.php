<?php

namespace Cyve\JsonDecoder\Tests;

use Cyve\JsonDecoder\Tests\Model\Blog;
use PHPUnit\Framework\TestCase;

class AggregateTest extends TestCase
{
    public function testDeserialize()
    {
        $aggregate = Blog::createDummy();
        $json = json_encode($aggregate);
        $result = Blog::jsonDeserialize($json);

        $this->assertEquals($aggregate, $result);
    }
}


