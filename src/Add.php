<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

trait Add 
{
    function add(...$params)
    {
        $add = self::curry(function($v1, $v2) {
            return $v1 + $v2;
        });
        return $add(...$params);
    }
}
