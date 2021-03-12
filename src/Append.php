<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

use InvalidArgumentException;
use Traversable;

trait Append 
{
    function append(...$params)
    {
        $append = self::curry(function($acc, $val) {
            if(is_array($acc))
            {
                $out = $acc;
                $out[] = $val;
            }
            else if(self::isTraversable($acc) || self::isGenerator($acc))
            {
                $fn = function() use($val) {
                    yield $val;
                };
                $out = self::generatorToIterable($fn);
            }
            else
            {
                throw new InvalidArgumentException("'acc' must be of type array or traversable");
            }
            return $out;
        });
        return $append(...$params);
    }
}
