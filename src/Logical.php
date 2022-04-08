<?php

/* 
 * (c) Matthew Taylor
 */

namespace FPHP;

trait Logical
{
    public static function allPass(callable ...$args)
    {
        return function($v) use($args)
        {
            foreach($args as $fn)
            {
                if(!$fn($v))
                {
                    return false;
                }
            }
            return true;
        };
    }

    public static function anyPass(callable ...$args)
    {
        return function($v) use($args)
        {
            foreach($args as $fn)
            {
                if($fn($v))
                {
                    return true;
                }
            }
            return false;
        };
    }

    public static function andd(...$args)
    {
        $and = self::curry(function(bool $a, bool $b) {
            return $a && $b;
        });
        return $and(...$args);
    }

    public static function orr(...$args)
    {
        $or = self::curry(function(bool $a, bool $b) {
            return $a || $b;
        });
        return $or(...$args);
    }

    public static function both(...$args)
    {
        $both = self::curry(function(callable $fn1, callable $fn2) {
            return function(...$args) use($fn1, $fn2) {
                return $fn1(...$args) && $fn2(...$args);
            };
        });
        return $both(...$args);
    }

    public static function either(...$args)
    {
        $either = self::curry(function(callable $fn1, callable $fn2) {
            return function(...$args) use($fn1, $fn2) {
                return $fn1(...$args) || $fn2(...$args);
            };
        });
        return $either(...$args);
    }

    public static function not(...$args)
    {
        $not = self::curry(function(bool $a) {
            return !$a;
        });
        return $not(...$args);
    }
}