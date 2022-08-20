<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait InTo
{
    public static function inTo($initial, callable $transducer, $collection)
    {
        return self::transduce($transducer, fn($acc, $v) => self::append($acc, $v), $initial, $collection);
    }

    public static function inToAssoc($initial, callable $transducer, $collection)
    {
        return self::transduce($transducer, fn($acc, $v, $k) => self::assoc($acc, $v, $k), $initial, $collection);
    }
}