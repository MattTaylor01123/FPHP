<?php

/* 
 * (c) Matthew Taylor
 */

namespace src;

use ArrayIterator;
use Exception;
use ReflectionFunction;
use src\utilities\IterableGenerator;

trait Functions
{
    public static function curry(Callable $func)
    {
        $refFunc = new ReflectionFunction($func);
        $arity = $refFunc->getNumberOfParameters();
        return self::curryN($func, $arity, false);
    }

    public static function curryN(Callable $func, int $arity, bool $reverseParams, ...$capturedArgs)
    {
        $outFunc = function(...$args) use($func, $arity, $reverseParams, $capturedArgs)
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
                return $func(...array_slice($noPlaceholders, 0, $arity));
            }
            else
            {
                return self::curryN($func, $arity, $reverseParams, ...$new);
            }
        };
        return $outFunc;
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
        return function(...$args) use($func) {
            return !$func(...$args);
        };
    }

    public static function flipN(callable $fn, int $arity)
    {
        return self::curryN($fn, $arity, true);
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
            $func = self::buildFixedArityFunc($arity + 1, function(...$args) use($arity, $methodName) {
                $object = $args[$arity];
                $args = array_slice($args, 0, $arity);
                return $object->$methodName(...$args);
            });
            return self::curry($func);
        });
        return $invoker(...$args);
    }


    public static function partial(Callable $func, ...$args)
    {
        $refFunc = new ReflectionFunction($func);
        $arity = $refFunc->getNumberOfParameters();
        $argCount = count($args);
        $missingArgs = $arity - $argCount;

        return self::buildFixedArityFunc(max(0, $missingArgs), function(...$newArgs) use($args, $func) {
            return $func(...array_merge($args, $newArgs));
        });
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
    
    public static function compose(callable ...$funcs)
    {
        return function(...$args) use($funcs)
        {
            $out = null;
            $first = true;
            for($i = count($funcs) - 1; $i >= 0; $i--)
            {
                if($first)
                {
                    $first = false;
                    $out = ($funcs[$i])(...$args);
                }
                else
                {
                    $out = ($funcs[$i])($out);
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

    public static function tapT(callable $func)
    {
        return fn(callable $step) => self::multiArityfunction(
            fn() => $step(),
            fn($acc) => $step($acc),
            function($acc, $v, $k) use($func, $step) {
                $func($v, $k);
                return $step($acc, $v, $k);
            }
        );
    }

    public static function tap(...$args)
    {
        $tap = self::curry(function(callable $func, $value) {
            return self::transduce(self::tapT($func), self::defaultStep($value), self::emptied($value), $value);
        });

        return $tap(...$args);
    }

    private static function buildFixedArityFunc(int $arity, callable $func)
    {
        switch($arity)
        {
            case 0:
                return function() use($func) { return $func(); };
            case 1:
                return function($a0) use($func) { return $func($a0); };
            case 2:
                return function($a0, $a1) use($func) { return $func($a0, $a1); };
            case 3:
                return function($a0, $a1, $a2) use($func) { return $func($a0, $a1, $a2); };
            case 4:
                return function($a0, $a1, $a2, $a3) use($func) { return $func($a0, $a1, $a2, $a3); };
            case 5:
                return function($a0, $a1, $a2, $a3, $a4) use($func) { return $func($a0, $a1, $a2, $a3, $a4); };
            case 6:
                return function($a0, $a1, $a2, $a3, $a4, $a5) use($func) { return $func($a0, $a1, $a2, $a3, $a4, $a5); };
            case 7:
                return function($a0, $a1, $a2, $a3, $a4, $a5, $a6) use($func) { return $func($a0, $a1, $a2, $a3, $a4, $a5, $a6); };
            case 8:
                return function($a0, $a1, $a2, $a3, $a4, $a5, $a6, $a7) use($func) { return $func($a0, $a1, $a2, $a3, $a4, $a5, $a6, $a7); };
            case 9:
                return function($a0, $a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8) use($func) { return $func($a0, $a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8); };
            case 10:
                return function($a0, $a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8, $a9) use($func) { return $func($a0, $a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8, $a9); };
            default:
                throw new Exception("Arity must be non-negative integer and less than or equal to 10");
        }
    }

    public static function walk(...$args)
    {
        $walk = self::curry(function(callable $func, iterable $iterable) {
            if(method_exists($iterable, "walk"))
            {
                return $iterable->walk($func);
            }
            else
            {
                $generator = function() use($iterable, $func) {
                    foreach($iterable as $k => $v)
                    {
                        $func($v, $k);
                        yield $k => $v;
                    }
                };
                return self::generatorToIterable($generator);
            }
        });
        return $walk(...$args);
    }

    public static function collect(...$args)
    {
        $collect = self::curry(function($iterable){
            if(is_array($iterable))
            {
                return $iterable;
            }
            else
            {
                // implicit TRUE means repeated keys get overridden
                // but FALSE would mean keys not returned
                return iterator_to_array($iterable);
            }
        });
        return $collect(...$args);
    }

    public static function generatorToIterable($generator)
    {
        return new IterableGenerator($generator);
    }

    public static function multiArityFunction(callable ...$fns)
    {
        $arityMap = array();
        foreach($fns as $fn)
        {
            $refFn = new \ReflectionFunction($fn);
            $arity = $refFn->getNumberOfParameters();
            $arityMap[$arity] = $fn;
        }

        return function(...$args) use($arityMap)
        {
            $argCount = count($args);
            if(array_key_exists($argCount, $arityMap))
            {
                return ($arityMap[$argCount])(...$args);
            }
            else
            {
                throw new ArgumentCountError("Invalid number of arguments for multi arity function");
            }
        };
    }
}