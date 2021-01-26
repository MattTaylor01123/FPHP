<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

trait Add 
{
    function add(...$params)
    {
        $add = R::curry(function($v1, $v2) {
            return $v1 + $v2;
        });
        return $add(...$params);
    }
}
