<?php

/* 
 * (c) Matthew Taylor
 */

namespace FPHP;

trait Relational
{
    public static function lt(...$args)
    {
        $lt = self::curry(fn ($a, $b) => $a < $b);
        return $lt(...$args);
    }

    public static function lte(...$args)
    {
        $lt = self::curry(fn ($a, $b) => $a <= $b);
        return $lt(...$args);
    }

    public static function gt(...$args)
    {
        $lt = self::curry(fn ($a, $b) => $a > $b);
        return $lt(...$args);
    }

    public static function gte(...$args)
    {
        $lt = self::curry(fn ($a, $b) => $a >= $b);
        return $lt(...$args);
    }

    public static function eq(...$args)
    {
        $eq = self::curry(fn ($a, $b) => $a === $b);
        return $eq(...$args);
    }

    public static function neq(...$args)
    {
        $neq = self::curry(fn ($a, $b) => $a !== $b);
        return $neq(...$args);
    }
}