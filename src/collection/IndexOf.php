<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

use InvalidArgumentException;

trait IndexOf
{
    public static function indexOf($needle, $target)
    {
        if(is_object($target) && method_exists($target, "indexOf"))
        {
            return $target->indexOf();
        }
        elseif(is_array($target))
        {
            return array_search($needle, $target, true) ?: -1;
        }
        elseif(is_iterable($target))
        {
            return self::reduce(fn($acc, $v, $k) => $v === $needle ? new Reduced($k) : -1, -1, $target);
        }
        else
        {
            throw new InvalidArgumentException("'target' must have method 'indexOf' or be an iterable.");
        }
    }
}