<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

trait Dec
{
    public static function dec()
    {
        return function($v) {
            return $v - 1;
        };
    }
}