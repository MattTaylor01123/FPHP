<?php

/*
 * (c) Matthew Taylor
 */
namespace src\collection;

use InvalidArgumentException;
use Traversable;

trait Pluck
{
    public static function pluck(string $propName, iterable $iterable)
    {
        if(is_object($iterable) && method_exists($iterable, "pluck"))
        {
            $out = $iterable->pluck($propName);
        }
        else if(is_array($iterable))
        {
            $out = array_column($iterable, $propName);
        }
        else if($iterable instanceof Traversable)
        {
            $out = self::map(fn($o) => self::prop($propName, $o), $iterable);
        }
        else
        {
            throw new InvalidArgumentException(
                "unrecognised iterable"
            );
        }
        return $out;
    }
}
