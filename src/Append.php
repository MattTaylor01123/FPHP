<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

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
            else if($acc instanceof \Traversable)
            {
                $fn = function() use($acc, $val) {
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
        });
        return $append(...$params);
    }
}
