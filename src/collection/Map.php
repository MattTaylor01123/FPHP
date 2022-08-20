<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

use InvalidArgumentException;

trait Map
{
    public static function mapT(callable $func, callable $step)
    {
        return fn($acc, $v, $k) => $step($acc, $func($v, $k), $k);
    }

    public static function map(callable $func, $coll)
    {
        if(is_object($coll) && method_exists($coll, "map"))
        {
            $out = $coll->map($func);
        }
        // array_map callback doesn't support keys
        // always use "assoc" for step function as we can't tell if a traversable is
        // associative or not without iterating it, and we can't do that in case it
        // is infinite. Map preserves keys anyway, so using assoc is fine.
        else if( is_object($coll) || is_array($coll) || self::isTraversable($coll) || self::isGenerator($coll))
        {
            $out = self::transduce(
                fn($step) => self::mapT($func, $step),
                fn($acc, $v, $k) => self::assoc($acc, $v, $k),
                self::emptied($coll),
                $coll
            );
        }
        else
        {
            throw new InvalidArgumentException("target must be one of array, stdClass, generator, functor.");
        }
        return $out;
    }
}