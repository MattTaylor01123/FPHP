<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait Matches
{
    public static function matchT(...$args)
    {
        $matchT = self::curry(function($criteria, $step) {
            return self::filterT(function($v) use($criteria) {
                //error_log(print_r($v, true));
                return self::all(function($func, $field) use($v) {
                    return $func(self::prop($field, $v));
                }, $criteria);
            }, $step);
        });
        return $matchT(...$args);
    }

    public static function match(...$args)
    {
        $fnMatch = self::curry(function(iterable $criteria, iterable $target) {
            if(is_object($target) && method_exists($target, "match"))
            {
                $out = $target->match($criteria);
            }
            else if(is_array($target) || is_object($target) || self::isTraversable($target) || self::isGenerator($target))
            {
                $out = self::transduce(
                    self::matchT($criteria),
                    self::defaultStep($target),
                    self::emptied($target),
                    $target
                );
            }
            else
            {
                throw new InvalidArgumentException(
                    "target must be one of array, stdClass, generator, functor."
                );
            }
            return $out;
        });
        return $fnMatch(...$args);
    }
}
