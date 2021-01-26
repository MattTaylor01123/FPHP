<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

use InvalidArgumentException;
use stdClass;
use Traversable;

trait Filtering
{
    public static function filter(...$args)
    {
        $filter = self::curry(function(callable $func, $target) {
            $transducer = fn($step) =>
                            fn($acc, $v, $k) => $func($v, $k) ? $step($acc, $v, $k) : $acc;
            if(method_exists($target, "filter"))
            {
                return $target->filter($func);
            }
            else if(is_callable($target))
            {
                // if target is a transform function, return a transducer
                $step = $target;
                return $transducer($step);
            }
            else if(self::isSequentialArray($target))
            {
                return array_values(array_filter($target, $func));
            }
            else if (is_array($target))
            {
                return array_filter($target, $func, ARRAY_FILTER_USE_BOTH );
            }
            else if($target instanceof stdClass)
            {
                return self::transduce($transducer, self::assoc(), new stdClass(), $target);
            }
            else if($target instanceof Traversable)
            {
                return self::transformTraversable($transducer, $target);
            }
            else
            {
                throw new InvalidArgumentException(
                    "target must be one of array, stdClass, generator, " .
                    "functor, or transform function"
                );
            }
        });
        return $filter(...$args);
    }

    public static function reject(...$args)
    {
        $reject = self::curry(function(callable $func, iterable $target) {
            return self::filter(self::complement($func), $target);
        });
        return $reject(...$args);
    }

    public static function take(...$args)
    {
        $take = self::curry(function(int $count, iterable $iterable) {
            $generator = function() use($count, $iterable) {
                $i = 0;
                foreach($iterable as $key => $value)
                {
                    if($i < $count)
                    {
                        yield $key => $value;
                        $i++;
                    }
                    else
                    {
                        break;
                    }
                }
            };
            return self::generatorToIterable($generator);
        });
        return $take(...$args);
    }
}