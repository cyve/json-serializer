<?php

namespace Cyve\JsonDecoder\Tests\Exception;

use Cyve\JsonDecoder\Exception\DenormalizationException;
use PHPUnit\Framework\TestCase;

class DenormalizationExceptionTest extends TestCase
{
    public function testCreateException()
    {
        $exception = new DenormalizationException('name', 'invalid type (must be of type string, null given)');

        $this->assertEquals('name', $exception->getProperty());
        $this->assertEquals('name: invalid type (must be of type string, null given)', $exception->getMessage());
    }

    public function testCreateNestedException()
    {
        $nestedException = new DenormalizationException('name', 'invalid type (must be of type string, null given)');
        $exception = new DenormalizationException('user', null, $nestedException);

        $this->assertEquals('user.name', $exception->getProperty());
        $this->assertEquals('user.name: invalid type (must be of type string, null given)', $exception->getMessage());
    }
}
