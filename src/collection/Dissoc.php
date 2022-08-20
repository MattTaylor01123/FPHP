<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

use InvalidArgumentException;

trait Dissoc
{
    public static function dissoc($acc, $propName)
    {
        if(is_array($acc))
        {
            $out = $acc;
            unset($out[$propName]);
        }
        else if(self::isTraversable($acc) || self::isGenerator($acc))
        {
            $fn = function() use($propName, $acc) {
                foreach($acc as $k => $v)
                {
                    if($k !== $propName)
                    {
                        yield $k => $v;
                    }
                }
            };
            $out = self::generatorToIterable($fn);
        }
        else if(is_object($acc))
        {
            $out = clone $acc;
            unset($out->$propName);
        }
        else
        {
            throw new InvalidArgumentException("'acc' must be of type array, traversable, or object");
        }
        return $out;
    }
}