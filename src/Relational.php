<?php

/* 
 * (c) Matthew Taylor
 */

namespace FPHP;

trait Relational
{
    public static function lt(...$args)
    {
        $lt = self::curry(function($a, $b) {
            if(is_object($b) && method_exists($b, "lt"))
            {
                return $b->lt($a);
            }
            if(is_object($a) && method_exists($a, "gt"))
            {
                return $a->gt($b);
            }
            return $a < $b;
        });
        return $lt(...$args);
    }

    public static function lte(...$args)
    {
        $lte = self::curry(function($a, $b) {
            if(is_object($b) && method_exists($b, "lte"))
            {
                return $b->lte($a);
            }
            if(is_object($a) && method_exists($a, "gte"))
            {
                return $a->gte($b);
            }
            return $a <= $b;
        });
        return $lte(...$args);
    }

    public static function gt(...$args)
    {
        $gt = self::curry(function($a, $b) {
            if(is_object($b) && method_exists($b, "gt"))
            {
                return $b->gt($a);
            }
            if(is_object($a) && method_exists($a, "lt"))
            {
                return $a->lt($b);
            }
            return $a > $b;
        });
        return $gt(...$args);
    }

    public static function gte(...$args)
    {
        $gte = self::curry(function($a, $b) {
            if(is_object($b) && method_exists($b, "gte"))
            {
                return $b->gte($a);
            }
            if(is_object($a) && method_exists($a, "lte"))
            {
                return $a->lte($b);
            }
            return $a >= $b;
        });
        return $gte(...$args);
    }

    public static function eq(...$args)
    {
        $eq = self::curry(function($a, $b) {
            if(is_object($b) && method_exists($b, "eq"))
            {
                return $b->eq($a);
            }
            if(is_object($b) && method_exists($b, "equals"))
            {
                return $b->equals($a);
            }
            if(is_object($a) && method_exists($a, "eq"))
            {
                return $a->eq($b);
            }
            if(is_object($a) && method_exists($a, "equals"))
            {
                return $a->equals($b);
            }
            return $a === $b;
        });
        return $eq(...$args);
    }

    public static function neq(...$args)
    {
        $neq = self::curry(function($a, $b) {
            if(is_object($b) && method_exists($b, "neq"))
            {
                return $b->neq($a);
            }
            if(is_object($a) && method_exists($a, "neq"))
            {
                return $a->neq($b);
            }
            return $a !== $b;
        });
        return $neq(...$args);
    }
}