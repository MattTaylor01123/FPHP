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
                return $b->gt($a);
            }
            if(is_object($a) && method_exists($a, "gt"))
            {
                return $a->lt($b);
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
                return $b->gte($a);
            }
            if(is_object($a) && method_exists($a, "gte"))
            {
                return $a->lte($b);
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
                return $b->lt($a);
            }
            if(is_object($a) && method_exists($a, "lt"))
            {
                return $a->gt($b);
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
                return $b->lte($a);
            }
            if(is_object($a) && method_exists($a, "lte"))
            {
                return $a->gte($b);
            }
            return $a >= $b;
        });
        return $gte(...$args);
    }

    public static function eq(...$args)
    {
        $eq = self::curry(function($v1, $v2) {
            if(is_object($v2) && method_exists($v2, "eq"))
            {
                return $v2->eq($v1);
            }
            if(is_object($v2) && method_exists($v2, "equals"))
            {
                return $v2->equals($v1);
            }
            if(is_object($v1) && method_exists($v1, "eq"))
            {
                return $v1->eq($v2);
            }
            if(is_object($v1) && method_exists($v1, "equals"))
            {
                return $v1->equals($v2);
            }

            if($v1 === $v2)
            {
                return true;
            }
            $t1 = gettype($v1);
            $t2 = gettype($v2);
            if($t1 !== $t2)
            {
                return false;
            }
            if(self::isIterable($v1) || $v1 instanceof \stdClass)
            {
                foreach($v1 as $k => $v)
                {
                    if(!self::propEq($k, $v, $v2))
                    {
                        return false;
                    }
                }
                
                foreach($v2 as $k => $v)
                {
                    if(!self::propEq($k, $v, $v1))
                    {
                        return false;
                    }
                }
                return true;
            }
            return false;
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