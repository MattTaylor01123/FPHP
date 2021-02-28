<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

use InvalidArgumentException;
use stdClass;
use Traversable;

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
        $filter = self::curry(function(callable $func, $target) {
            $transducer = fn($step) =>
                            fn($acc, $v, $k) => $func($v, $k) ? $step($acc, $v, $k) : $acc;
            if(method_exists($target, "filter"))
            {
                $out = $target->filter($func);
            }
            else if(self::isSequentialArray($target))
            {
                $out = array_values(array_filter($target, $func));
            }
            else if (is_array($target))
            {
                $out = array_filter($target, $func, ARRAY_FILTER_USE_BOTH );
            }
            else if($target instanceof stdClass)
            {
                $out = self::transduce(self::filterT($func), self::assoc(), new stdClass(), $target);
            }
            else if($target instanceof Traversable)
            {
                $out = self::transformTraversable($transducer, $target);
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
        return $filter(...$args);
    }
}