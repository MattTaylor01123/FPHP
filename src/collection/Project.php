<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait Project
{
    public static function project(array $properties, iterable $iterable)
    {
        if(is_object($iterable) && method_exists($iterable, "project"))
        {
            $out = $iterable->project($properties);
        }
        else
        {
            $out = self::map(fn($v) => self::pick($properties, $v), $iterable);
        }
        return $out;
    }
}