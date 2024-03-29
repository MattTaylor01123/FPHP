<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait Pick
{
    public static function pick(iterable $properties, $target)
    {
        return self::filterK(fn($v, $k) => self::includes($k, $properties), $target);
    }
}