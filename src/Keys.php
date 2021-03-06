<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

trait Keys
{
    public static function keysT(...$args)
    {
        $keysT = self::curry(function($step) {
            return fn($acc, $v, $k) => $step($acc, $k);
        });
        return $keysT(...$args);
    }

    public static function keys(...$args)
    {
        $keys = self::curry(function($target) {
            $transduceInto = self::transduce(self::keysT(), self::append(), self::__(), $target);
            if(method_exists($target, "keys"))
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
        });
        return $keys(...$args);
    }
}