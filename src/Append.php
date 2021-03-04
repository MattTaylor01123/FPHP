<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

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
