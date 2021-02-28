<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

trait AppendK
{
    function appendK(...$params)
    {
        $appendK = self::curry(function(array $arr, $val, $key) {
            $out = $arr;
            $out[$key] = $val;
            return $out;
        });
        return $appendK(...$params);
    }
}
