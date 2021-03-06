<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

trait Inc
{
    public static function inc()
    {
        return function($x) {
            return $x + 1;
        };
    }
}