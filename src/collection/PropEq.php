<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait PropEq 
{
    public static function propEq(...$args)
    {
        $propEq = self::curry(function($propName, $val, $target) {
            return self::hasProp($propName, $target) &&
                   self::eq(self::prop($propName, $target), $val);
        });
        return $propEq(...$args);
    }
}
