<?php

/* 
 * (c) Matthew Taylor
 */

namespace FPHP;

use ArrayIterator;
use ReflectionFunction;

trait Functions
{
    public static function curry(Callable $func)
    {
        $refFunc = new ReflectionFunction($func);
        $arity = $refFunc->getNumberOfParameters();
        return self::partial($func, $arity, false);
    }

    public static function partial(Callable $func, int $arity, bool $reverseParams, ...$capturedArgs)
    {
        return function(...$args) use($func, $arity, $reverseParams, $capturedArgs)
        {
            $orderedArgs = $reverseParams ? array_reverse($args) : $args;
            $itArgs = new ArrayIterator($orderedArgs);
            $new = array();
            foreach($capturedArgs as $curr)
            {
                if($curr === self::__() && $itArgs->valid())
                {
                    $new[] = $itArgs->current();
                    $itArgs->next();
                }
                else
                {
                    $new[] = $curr;
                }
            }
            while($itArgs->valid())
            {
                $new[] = $itArgs->current();
                $itArgs->next();
            }
            $noPlaceholders = array_filter($new, function($e) {
                return $e !== self::__();
            });

            if(count($noPlaceholders) >= $arity)
            {
                return $func(...$noPlaceholders);
            }
            else
            {
                return self::partial($func, $arity, $reverseParams, ...$new);
            }
        };
    }

    public static function __()
    {
        return "__";
    }

    public static function always($v)
    {
        return function() use($v) {
            return $v;
        };
    }

    public static function apply(...$args)
    {
        $apply = self::curry(function(callable $func, array $params) {
            return $func(...$params);
        });
        return $apply(...$args);
    }

    public static function complement(callable $func)
    {
        $refFunc = new ReflectionFunction($func);
        $arity = $refFunc->getNumberOfParameters();

        return self::partial(function(...$args) use($func) {
            return !$func(...$args);
        }, $arity, false);
    }

    public static function flipN(callable $fn, int $arity)
    {
        return self::partial($fn, $arity, true);
    }

    public static function identity(...$args)
    {
        $identity = self::curry(function($v) {
            return $v;
        });
        return $identity(...$args);
    }

    public static function invoker(...$args)
    {
        $invoker = self::curry(function(int $arity, string $methodName) {
            return self::partial(function(...$args) use($methodName, $arity) {
                $object = $args[$arity];
                $args = array_slice($args, 0, $arity);
                return $object->$methodName(...$args);
            }, $arity + 1, false);
        });
        return $invoker(...$args);
    }
    
    public static function pipe(callable ...$funcs)
    {
        return function(...$args) use($funcs)
        {
            $first = true;
            $out = null;
            foreach($funcs as $fn)
            {
                if($first)
                {
                    $first = false;
                    $out = $fn(...$args);
                }
                else
                {
                    $out = $fn($out);
                }
            }
            return $out;
        };
    }

    public static function pipex($firstParameter, callable ...$funcs)
    {
        $fn = self::pipe(...$funcs);
        return $fn($firstParameter);
    }

    public static function transduce(...$args)
    {
        $transduce = self::curry(function(callable $transducer, callable $step, $initial, $collection)
        {
            if($initial instanceof \Traversable)
            {
                return self::transformTraversable($transducer, $step, $collection);
            }
            else
            {
                return self::reduce($transducer($step), $initial, $collection);
            }
        });
        return $transduce(...$args);
    }
}