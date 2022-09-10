<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

trait PickAll
{
    public static function pickAll(iterable $props, $target)
    {
        // TODO - what about items that are missing?
        return self::filter(fn($v, $k) => self::includes($k, $props), $target);
    }
}