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
        $indexBy = self::curry(function(callable $func, iterable $iterable) {
            $transducer = fn($step) =>
                            fn($acc, $v, $k) => $step($acc, $v, $func($v, $k));
            if(method_exists($iterable, "indexBy"))
            {
                $out = $iterable->indexBy($func);
            }
            else if(is_array($iterable))
            {
                $out = self::transduce($transducer, self::concatK(), [], $iterable);
            }
            else if($iterable instanceof Traversable)
            {
                $out = self::transformTraversable($transducer, $iterable);
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
        $map = self::curry(function(callable $func, $target) {
            $transducer = fn($step) =>
                            fn($acc, $v, $k) => $step($acc, $func($v, $k), $k);
            if(method_exists($target, "map"))
            {
                $out = $target->map($func);
            }
            else if(is_callable($target))
            {
                // if target is a transform function, return a transducer
                $step = $target;
                $out = $transducer($step);
            }
            else if($target instanceof stdClass)
            {
                $out = self::transduce($transducer, self::assoc(), new stdClass(), $target);
            }
            else if(is_array($target))
            {
                $out = self::transduce($transducer, self::concatK(), [], $target);
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
