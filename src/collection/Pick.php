<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

trait Pick
{
    public static function pick(iterable $properties, $target) {
        return self::filter(fn($v, $k) => self::includes($k, $properties), $target);
    }
}