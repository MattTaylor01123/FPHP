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
            return self::filter(function($v, $k) use($properties) {
                return self::includes($k, $properties);
            }, $target);
        });
        return $pick(...$args);
    }
}