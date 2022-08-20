<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait HasProps
{
    public static function hasProps(...$args)
    {
        $hasProp = self::curry(function(array $propNames, $target) {
            return self::all(fn($p) => self::hasProp($p, $target), $propNames);
        });
        return $hasProp(...$args);
    }
}