<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

use RamdaPHP\RamdaPHP as R;

trait PickAll
{
    public static function pickAll(...$args)
    {
        // TODO - what about items that are missing?
        $pickAll = R::curry(function(iterable $props, $target) {
            return R::filter(fn($v, $k) => R::includes($k, $props), $target);
        });
        return $pickAll(...$args);
    }
}