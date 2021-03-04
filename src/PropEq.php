<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

trait PropEq 
{
    public static function propEq(...$args)
    {
        $propEq = self::curry(function($propName, $val, $target) {
            $v = self::prop($propName, $target);
            return self::equals($v, $val);
        });
        return $propEq(...$args);
    }
}
