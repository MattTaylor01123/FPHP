<?php

/*
 * (c) Matthew Taylor
 */
namespace FPHP;

use InvalidArgumentException;
use Traversable;

trait Pluck
{
    public static function pluck(...$args)
    {
        $pluck = self::curry(function(string $propName, iterable $iterable) {
            if(method_exists($iterable, "pluck"))
            {
                $out = $iterable->pluck($propName);
            }
            else if(is_array($iterable))
            {
                $out = array_column($iterable, $propName);
            }
            else if($iterable instanceof Traversable)
            {
                $out = self::map(self::prop($propName), $iterable);
            }
            else
            {
                throw new InvalidArgumentException(
                    "unrecognised iterable"
                );
            }
            return $out;
        });
        return $pluck(...$args);
    }
}
