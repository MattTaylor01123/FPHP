<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

trait Find
{
    public static function find(callable $predicate, iterable $iterable)
    {
        if(is_object($iterable) && method_exists($iterable, "find"))
        {
            return $iterable->find($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? new Reduced($v) : null, null, $iterable);
        }
    }

    public static function findIndex(callable $predicate, iterable $iterable) : int
    {
        if(is_object($iterable) && method_exists($iterable, "findIndex"))
        {
            return $iterable->findIndex($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? new Reduced($k) : -1, -1, $iterable);
        }
    }
}