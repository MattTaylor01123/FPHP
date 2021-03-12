<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

use InvalidArgumentException;
use Traversable;

trait Concat
{
    /**
     * Join, one after the other, strings, sequential arrays, and traversables
     * and generators.
     */
    public static function concat(...$args)
    {
        $concat = self::curry(function($v1, $v2) {
            $v1t = gettype($v1);
            $v2t = gettype($v2);
            $v1type = $v1t === "object" ? get_class($v1) : $v1t;
            $v2type = $v2t === "object" ? get_class($v2) : $v2t;

            if($v1type !== $v2type)
            {
                throw new InvalidArgumentException("v1 and v2 must be of the same type");
            }

            if(method_exists($v1, "concat"))
            {
                $out = $v1->concat($v2);
            }
            else if(is_string($v1) && is_string($v2))
            {
                $out = $v1.$v2;
            }
            else if(is_array($v1) && is_array($v2))
            {
                $out = array_merge(array_values($v1), array_values($v2));
            }
            else if($v1 instanceof Traversable && $v2 instanceof Traversable)
            {
                $fn = function() use($v1, $v2) {
                    yield from $v1;
                    yield from $v2;
                };
                $out = self::generatorToIterable($fn);
            }
            else
            {
                throw new InvalidArgumentException("v1 and v2 of unhandled type");
            }
            return $out;
        });
        return $concat(...$args);
    }
}