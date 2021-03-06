<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

use InvalidArgumentException;
use Traversable;

trait Map
{
    public static function mapT(...$args)
    {
        $mapT = self::curry(function(callable $func, callable $step) {
            return fn($acc, $v, $k) => $step($acc, $func($v, $k), $k);
        });
        return $mapT(...$args);
    }

    public static function map(...$args)
    {
        $map = self::curry(function(callable $func, $coll) {
            if(method_exists($coll, "map"))
            {
                $out = $coll->map($func);
            }
            else if($coll instanceof Traversable || is_object($coll) || is_array($coll))
            {
                $out = self::transduce(self::mapT($func), self::assoc(), self::emptied($coll), $coll);
            }
            else
            {
                throw new InvalidArgumentException(
                    "target must be one of array, stdClass, generator, functor."
                );
            }
            return $out;
        });
        return $map(...$args);
    }
}