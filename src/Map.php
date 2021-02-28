<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

use InvalidArgumentException;
use stdClass;
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
            else if($coll instanceof Traversable)
            {
                $out = self::transformTraversable(self::mapT($func), $coll);
            }
            else if(is_object($coll))
            {
                $out = self::transduce(self::mapT($func), self::assoc(), new stdClass(), $coll);
            }
            else if(is_array($coll))
            {
                $out = self::transduce(self::mapT($func), self::assoc(), [], $coll);
            }
            else
            {
                throw new InvalidArgumentException(
                    "target must be one of array, stdClass, generator, " .
                    "functor, or transform function"
                );
            }
            return $out;
        });
        return $map(...$args);
    }
}