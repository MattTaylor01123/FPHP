<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

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
}