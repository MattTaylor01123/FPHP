<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

use InvalidArgumentException;
use Traversable;

trait IndexBy
{
    public static function indexByT(...$args)
    {
        $indexByT = self::curry(function(callable $func, callable $step) {
            return fn($acc, $v, $k) => $step($acc, $v, $func($v, $k));
        });
        return $indexByT(...$args);
    }

    public static function indexBy(...$args)
    {
        $indexBy = self::curry(function(callable $func, $coll) {
            if(method_exists($coll, "indexBy"))
            {
                $out = $coll->indexBy($func);
            }
            else if(is_array($coll) || $coll instanceof Traversable)
            {
                $out = self::transduce(self::indexByT($func), self::assoc(), self::emptied($coll), $coll);
            }
            else
            {
                throw new InvalidArgumentException(
                    "unrecognised iterable"
                );
            }
            return $out;
        });
        return $indexBy(...$args);
    }
}