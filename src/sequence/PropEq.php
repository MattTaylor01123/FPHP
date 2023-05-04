<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait PropEq 
{
    public static function propEq(string $propName, $val, $target)
    {
        return self::hasProp($propName, $target) &&
               self::eq(self::prop($propName, $target), $val);
    }
}