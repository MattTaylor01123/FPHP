<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

trait Merge
{
    /*
     * concat but for indexed data structures
     */
    public static function merge(...$args)
    {
        $merge = self::curry(function($v1, $v2) {
            $v1t = gettype($v1);
            $v2t = gettype($v2);
            $v1type = $v1t === "object" ? get_class($v1) : $v1t;
            $v2type = $v2t === "object" ? get_class($v2) : $v2t;

            if($v1type !== $v2type)
            {
                throw new Exception("v1 and v2 must be of the same type");
            }

            if(method_exists($v1, "merge"))
            {
                $out = $v1->merge($v2);
            }
            else if(is_array($v1) && is_array($v2))
            {
                $out = array_merge($v1, $v2);
            }
            else if($v1 instanceof Traversable && $v2 instanceof Traversable)
            {
                $fn = function() use($v1, $v2) {
                    foreach($v1 as $k => $v)
                    {
                        yield $k => $v;
                    }
                    foreach($v2 as $k => $v)
                    {
                        yield $k => $v;
                    }
                };
                $out = self::generatorToIterable($fn);
            }
            else
            {
                throw new Exception("v1 and v2 of unhandled type");
            }
            return $out;
        });
        return $merge(...$args);
    }
}