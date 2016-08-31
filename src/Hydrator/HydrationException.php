<?php

namespace Grey\Quench\Hydrator;

class HydrationException extends \Exception
{
    public static function invalidEntity()
    {
        return new self;
    }

    public static function invalidRelationshipDefined()
    {
        return new self;
    }
}