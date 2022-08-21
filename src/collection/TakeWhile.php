<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait TakeWhile 
{
    public static function takeWhileT(callable $pred, callable $step)
    {
        $fin = false;
        return function($acc, $v, $k) use(&$fin, $pred, $step) {
            $fin = $fin || !$pred($v, $k);
            if(!$fin)
            {
                return $step($acc, $v, $k);
            }
            else
            {
                return new Reduced($acc);
            }
        };
    }
    
    public static function takeWhile(callable $pred, $target)
    {
        if(is_object($target) && method_exists($target, "takeWhile"))
        {
            return $target->takeWhile($pred);
        }
        else
        {
            // always use "assoc" for step function as we can't tell if a traversable is
            // associative or not without iterating it, and we can't do that in case it
            // is infinite. Take preserves keys anyway, so using assoc is fine.
            return self::transduce(
                fn($step) => self::takeWhileT($pred, $step),
                fn($acc, $v, $k) => self::assoc($acc, $v, $k),
                self::emptied($target),
                $target
            );
        }
    }
}
