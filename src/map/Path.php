<?php

/*
 * (c) Matthew Taylor
 */

namespace src\map;

trait Path 
{
    public static function path(iterable $path, $target)
    {
        return self::reduce(function($acc, $part) {
            if($acc)
            {
                return self::prop($part, $acc);
            }
            else
            {
                return new Reduced($acc);
            }
        }, $target, $path);
    }
}
