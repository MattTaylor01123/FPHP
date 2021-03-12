<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait Pick
{
    public static function pick(...$args)
    {
        $pick = self::curry(function(iterable $properties, $target) {
            return self::filter(fn($v, $k) => self::includes($k, $properties), $target);
        });
        return $pick(...$args);
    }
}