<?php

namespace Cyve\JsonDecoder\Exception;

class DenormalizationException extends \RuntimeException
{
    public function __construct(private string $property, ?string $message = null, ?\Throwable $previous = null)
    {
        if ($previous instanceof self) {
            $message = sprintf('%s.%s', $property, $previous->getMessage());
            $this->property = sprintf('%s.%s', $property, $previous->getProperty());
        } else {
            $message = sprintf('%s: %s', $property, $previous ? $previous->getMessage() : $message);
        }

        parent::__construct($message, 0, $previous);
    }

    public function getProperty(): string
    {
        return $this->property;
    }
}
