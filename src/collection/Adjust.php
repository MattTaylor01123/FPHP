<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait Adjust 
{
    public static function adjustT($idx, callable $transform, callable $step)
    {
        return fn($acc, $v, $k) => $step($acc, $k === $idx ? $transform($v, $k) : $v, $k);
    }

    public static function adjust($idx, callable $transform, $list) {
        return self::transduce(
            fn($step) => self::adjustT($idx, $transform, $step),
            // always use "assoc" for step function as we can't tell if a traversable is
            // associative or not without iterating it, and we can't do that in case it
            // is infinite. Adjust preserves keys anyway, so using assoc is fine.
            self::assoc(),
            self::emptied($list),
            $list
        );
    }
}
