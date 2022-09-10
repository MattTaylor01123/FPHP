<?php

/*
 * (c) Matthew Taylor
 */

namespace src;

trait Memoize 
{
    public static function memoize(callable $fn)
    {
        return function(...$args) use($fn) {
            static $prev = array();
            $v = self::find(fn($v) => self::propEq(0, $args, $v), $prev);
            if(!$v)
            {
                $out = $fn(...$args);
                $prev[] = [$args, $out];
            }
            else
            {
                $out = $v[1];
            }
            return $out;
        };
    }    
}
