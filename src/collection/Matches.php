<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait Matches
{
    public static function matchT($criteria, $step)
    {
        return self::filterT(function($v) use($criteria) {
            return self::all(function($func, $field) use($v) {
                return $func(self::prop($field, $v));
            }, $criteria);
        }, $step);
    }

    public static function match(iterable $criteria, iterable $target)
    {
        if(is_object($target) && method_exists($target, "match"))
        {
            $out = $target->match($criteria);
        }
        else if(is_array($target) || is_object($target) || self::isTraversable($target) || self::isGenerator($target))
        {
            $out = self::transduce(
                fn($step) => self::matchT($criteria, $step),
                self::defaultStep($target),
                self::emptied($target),
                $target
            );
        }
        else
        {
            throw new InvalidArgumentException("target must be one of array, stdClass, generator, functor.");
        }
        return $out;
    }
}
