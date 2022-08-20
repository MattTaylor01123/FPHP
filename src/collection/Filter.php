<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

use InvalidArgumentException;

trait Filter
{
    public static function filterT(...$args)
    {
        $filterT = self::curry(function(callable $func, callable $step) {
            return function($acc, $v, $k) use($func, $step) {
                return ($func($v, $k) ? $step($acc, $v, $k) : $acc);
            };
        });
        return $filterT(...$args);
    }

    public static function filter(...$args)
    {
        $filter = self::curry(function(callable $func, $coll) {
            if(is_object($coll) && method_exists($coll, "filter"))
            {
                $out = $coll->filter($func);
            }
            else if(self::isSequentialArray($coll))
            {
                $out = array_values(array_filter($coll, $func));
            }
            else if (is_array($coll))
            {
                $out = array_filter($coll, $func, ARRAY_FILTER_USE_BOTH );
            }
            else if(is_object($coll) || self::isTraversable($coll) || self::isGenerator($coll))
            {
                // already dealt with the case of col being a sequential array.
                // if it's an iterable (traversable / generator) we can't tell whether it is
                // associative or not. Err on the side of keeping the keys as they
                // can be stripped out later with values().
                $out = self::transduce(self::filterT($func), self::assoc(), self::emptied($coll), $coll);
            }
            else
            {
                throw new InvalidArgumentException(
                    "'coll' must be one of array, traversable, object, or object implementing filter"
                );
            }
            return $out;
        });
        return $filter(...$args);
    }
}