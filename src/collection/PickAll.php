<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait PickAll
{
    public static function pickAll(iterable $props, $target)
    {
        // TODO - what about items that are missing?
        return self::filter(fn($v, $k) => self::includes($k, $props), $target);
    }
}