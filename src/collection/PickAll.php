<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait PickAll
{
    public static function pickAll(...$args)
    {
        // TODO - what about items that are missing?
        $pickAll = self::curry(function(iterable $props, $target) {
            return self::filter(fn($v, $k) => self::includes($k, $props), $target);
        });
        return $pickAll(...$args);
    }
}