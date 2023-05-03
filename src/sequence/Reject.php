<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait Reject
{
    public static function reject(callable $func, iterable $target)
    {
        return self::filter(self::complement($func), $target);
    }
}