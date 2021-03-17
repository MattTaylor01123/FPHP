<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait TakeWhile 
{
    public static function takeWhileT(...$args)
    {
        $takeWhile = self::curry(function(callable $pred, callable $step) {
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
        });
        return $takeWhile(...$args);
    }
    
    public static function takeWhile(...$args)
    {
        $takeWhile = self::curry(function(callable $pred, $target) {
            if(method_exists($target, "take"))
            {
                return $target->takeWhile($pred);
            }
            else
            {
                return self::transduce(self::takeWhileT($pred), self::assoc(), self::emptied($target), $target);
            }
        });
        return $takeWhile(...$args);
    }
}
