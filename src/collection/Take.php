<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait Take
{
    public static function takeT(...$args)
    {
        $takeT = self::curry(function(int $count, callable $step) {
            $i = 0;
            return function($acc, $v, $k) use($step, $count, &$i) {
                $i++;
                if($i < $count)
                {
                    return $step($acc, $v, $k);
                }
                else if($i === $count)
                {
                    return new Reduced($step($acc, $v, $k));
                }
                else
                {
                    return new Reduced($acc);
                }
            };
        });
        return $takeT(...$args);
    }

    public static function take(...$args)
    {
        $take = self::curry(function(int $count, $target) {
            if(is_object($target) && method_exists($target, "take"))
            {
                return $target->take($count);
            }
            else
            {
                // always use "assoc" for step function as we can't tell if a traversable is
                // associative or not without iterating it, and we can't do that in case it
                // is infinite. Take preserves keys anyway, so using assoc is fine.
                return self::transduce(self::takeT($count), self::assoc(), self::emptied($target), $target);
            }
        });
        return $take(...$args);
    }
}