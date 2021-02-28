<?php

/*
 * (c) Matthew Taylor
 */
namespace RamdaPHP;

use InvalidArgumentException;
use stdClass;
use Traversable;

trait Mapping 
{
    public static function indexBy(...$args)
    {
        $indexBy = self::curry(function(callable $func, $coll) {
            $transducer = fn($step) =>
                            fn($acc, $v, $k) => $step($acc, $v, $func($v, $k));
            if(method_exists($coll, "indexBy"))
            {
                $out = $coll->indexBy($func);
            }
            else if(is_callable($coll))
            {
                // if target is a transform function, return a transducer
                $step = $coll;
                $out = $transducer($step);
            }
            else if(is_array($coll))
            {
                $out = self::transduce($transducer, self::appendK(), [], $coll);
            }
            else if($coll instanceof Traversable)
            {
                $out = self::transformTraversable($transducer, $coll);
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

    public static function map(...$args)
    {
        $map = self::curry(function(callable $func, $coll) {
            $transducer = fn($step) =>
                            fn($acc, $v, $k) => $step($acc, $func($v, $k), $k);
            if(method_exists($coll, "map"))
            {
                $out = $coll->map($func);
            }
            else if(is_callable($coll))
            {
                // if target is a transform function, return a transducer
                $step = $coll;
                $out = $transducer($step);
            }
            else if($coll instanceof stdClass)
            {
                $out = self::transduce($transducer, self::assoc(), new stdClass(), $coll);
            }
            else if(is_array($coll))
            {
                $out = self::transduce($transducer, self::appendK(), [], $coll);
            }
            else if($coll instanceof Traversable)
            {
                $out = self::transformTraversable($transducer, $coll);
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

    public static function pluck(...$args)
    {
        $pluck = self::curry(function(string $propName, iterable $iterable) {
            if(method_exists($iterable, "pluck"))
            {
                $out = $iterable->pluck($propName);
            }
            else if(is_array($iterable))
            {
                $out = array_column($iterable, $propName);
            }
            else if($iterable instanceof Traversable)
            {
                $out = self::map(self::prop($propName), $iterable);
            }
            else
            {
                throw new InvalidArgumentException(
                    "unrecognised iterable"
                );
            }
            return $out;
        });
        return $pluck(...$args);
    }
}
