<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

use InvalidArgumentException;

trait Filter
{
    public static function filterT(...$args)
    {
        $filterT = self::curry(function(callable $func, callable $step) {
            return fn($acc, $v, $k) => $func($v, $k) ? $step($acc, $v, $k) : $acc;
        });
        return $filterT(...$args);
    }

    public static function filter(...$args)
    {
        $filter = self::curry(function(callable $func, $coll) {
            if(method_exists($coll, "filter"))
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