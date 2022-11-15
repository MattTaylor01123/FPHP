<?php

/*
 * (c) Matthew Taylor
 */

namespace src\logic;

use src\collection\Reduced;

trait All
{
    public static function all(callable $fnPred, iterable $iterable) : bool
    {
        if(is_object($iterable) && method_exists($iterable, "all"))
        {
            return $iterable->all($fnPred);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) =>
                (!$fnPred($v, $k) ? new Reduced(false) : true), true, $iterable);
        }
    }

    public static function allPass(callable ...$args)
    {
        return fn($v) => self::all(fn($fn) => $fn($v), $args);
    }
}