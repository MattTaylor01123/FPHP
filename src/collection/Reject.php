<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait Reject
{
    public static function reject(...$args)
    {
        $reject = self::curry(function(callable $func, iterable $target) {
            return self::filter(self::complement($func), $target);
        });
        return $reject(...$args);
    }
}