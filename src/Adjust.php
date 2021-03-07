<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

trait Adjust 
{
    public static function adjustT(...$args)
    {
        $adjustT = self::curry(function($idx, callable $transform, callable $step) {
            return fn($acc, $v, $k) => $step($acc, $k === $idx ? $transform($v, $k) : $v, $k);
        });
        return $adjustT(...$args);
    }

    public static function adjust(...$params)
    {
        $adjust = self::curry(function($idx, callable $transform, $list) {
            return self::transduce(
                self::adjustT($idx, $transform),
                self::assoc(),
                self::emptied($list),
                $list
            );
        });
        return $adjust(...$params);
    }
}
