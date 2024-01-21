<?php

namespace Cyve\JsonDecoder\Tests;

use Cyve\JsonDecoder\Tests\Model\Blog;
use PHPUnit\Framework\TestCase;

class BlogTest extends TestCase
{
    public function testDeserialize()
    {
        $blog = Blog::createDummy();
        $json = json_encode($blog);

        $result = Blog::jsonDeserialize($json);

        $this->assertEquals($blog, $result);
    }
}


