<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

trait Append 
{
    function append(...$params)
    {
        $append = self::curry(function(array $arr, $val) {
            $out = $arr;
            $out[] = $val;
            return $out;
        });
        return $append(...$params);
    }
}
