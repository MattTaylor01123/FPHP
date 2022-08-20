<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

use InvalidArgumentException;

trait Append 
{
    public static function append($acc, $val)
    {
        if(is_array($acc))
        {
            $out = $acc;
            $out[] = $val;
        }
        else if(self::isTraversable($acc) || self::isGenerator($acc))
        {
            $fn = function() use($val, $acc) {
                yield from $acc;
                yield $val;
            };
            $out = self::generatorToIterable($fn);
        }
        else
        {
            throw new InvalidArgumentException("'acc' must be of type array or traversable");
        }
        return $out;
    }
}
