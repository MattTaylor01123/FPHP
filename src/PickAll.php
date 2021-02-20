<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

trait PickAll
{
    public static function pickAll(...$args)
    {
        // TODO - what about items that are missing?
        $pickAll = R::curry(function(iterable $props, $target) {
            return R::filter(fn($v, $k) => R::contains($k, $props), $target);
        });
        return $pickAll(...$args);
    }
}