<?php

namespace Cyve\JsonDecoder\Tests\Model;

enum Status: string
{
    case Draft = 'draft';
    case Published = 'published';
}
