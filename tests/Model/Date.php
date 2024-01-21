<?php

namespace Cyve\JsonDecoder\Tests\Model;

class Date extends \DateTimeImmutable implements \Stringable, \JsonSerializable
{
    public function __construct(
        public ?string $datetime = null,
    ) {
        parent::__construct($datetime ?? 'now');
    }

    public function __toString(): string {
        return $this->format(\DATE_ATOM);
    }

    public function jsonSerialize(): mixed
    {
        return (string) $this;
    }
}
