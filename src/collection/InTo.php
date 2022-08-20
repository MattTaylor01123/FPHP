<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait InTo
{
    public static function inTo(...$args)
    {
        $inTo = self::curry(function($initial, callable $transducer, $collection) {
            return self::transduce($transducer, fn($acc, $v) => self::append($acc, $v), $initial, $collection);
        });
        return $inTo(...$args);
    }

    public static function inToAssoc(...$args)
    {
        $inTo = self::curry(function($initial, callable $transducer, $collection) {
            return self::transduce($transducer, self::assoc(), $initial, $collection);
        });
        return $inTo(...$args);
    }
}