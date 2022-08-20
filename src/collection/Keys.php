<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait Keys
{
    public static function keysT(callable $step)
    {
        return fn($acc, $v, $k) => $step($acc, $k, 0);
    }

    public static function keys($target)
    {
        $transduceInto = fn($initial) => self::transduce(
            fn($step) => self::keysT($step),
            fn($acc, $v) => self::append($acc, $v),
            $initial,
            $target
        );
        if(is_object($target) && method_exists($target, "keys"))
        {
            $out = $target->keys();
        }
        else if(is_array($target))
        {
            $out = array_keys($target);
        }
        else if(is_iterable($target))
        {
            $out = $transduceInto(self::emptied($target));
        }
        else if(is_object($target))
        {
            $out = $transduceInto(self::emptied([]));
        }
        else
        {
            throw new InvalidArgumentException("'target' must be iterable or object");
        }
        return $out;
    }
}