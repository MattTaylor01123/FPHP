<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\logic;

use FPHP\collection\Reduced;

trait Any
{
    public static function any(callable $fnPred, iterable $iterable) : bool
    {
        if(is_object($iterable) && method_exists($iterable, "any"))
        {
            return $iterable->any($fnPred);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) =>
                ($fnPred($v, $k) ? new Reduced(true) : false), false, $iterable);
        }
    }

    public static function anyPass(callable ...$args)
    {
        return fn($v) => self::any(fn($fn) => $fn($v), $args);
    }
}