<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

trait Adjust 
{
    public static function adjust(...$params)
    {
        $adjust = self::curry(function($idx, callable $transform, $list) {
            $transducer = fn($step) => 
                fn($acc, $v, $k) => $step($acc, $k === $idx ? $transform($v, $k) : $v, $k);

            $empty = self::emptied($list);
            return self::transduce($transducer, self::assoc(), $empty, $list);
        });
        return $adjust(...$params);
    }
}
