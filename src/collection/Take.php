<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait Take
{
    public static function takeT(int $count, callable $step)
    {
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
    }

    public static function take(int $count, $target)
    {
        if(is_object($target) && method_exists($target, "take"))
        {
            return $target->take($count);
        }
        else
        {
            // always use "assoc" for step function as we can't tell if a traversable is
            // associative or not without iterating it, and we can't do that in case it
            // is infinite. Take preserves keys anyway, so using assoc is fine.
            return self::transduce(
                fn($step) => self::takeT($count, $step),
                fn($acc, $v, $k) => self::assoc($acc, $v, $k),
                self::emptied($target),
                $target
            );
        }
    }
}