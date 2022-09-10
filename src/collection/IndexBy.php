<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

use InvalidArgumentException;
use Traversable;

trait IndexBy
{
    public static function indexByT(callable $func, callable $step) {
        return fn($acc, $v, $k) => $step($acc, $v, $func($v, $k));
    }

    public static function indexBy(callable $func, $coll)
    {
        if(is_object($coll) && method_exists($coll, "indexBy"))
        {
            $out = $coll->indexBy($func);
        }
        else if(is_array($coll) || $coll instanceof Traversable)
        {
            $out = self::transduce(
                fn($step) => self::indexByT($func, $step),
                fn($acc, $v, $k) => self::assoc($acc, $v, $k),
                self::emptied($coll),
                $coll
            );
        }
        else
        {
            throw new InvalidArgumentException("unrecognised iterable");
        }
        return $out;
    }
}