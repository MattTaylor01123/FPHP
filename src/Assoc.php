<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

trait Assoc
{
    /*
     * appends a value to an indexed data structure (assoc array, traversable,
     * object).
     */
    public static function assoc(...$args)
    {
        $assoc = self::curry(function($target, $value, $propName) {
            if(is_array($target))
            {
                $out = $target;
                $out[$propName] = $value;
            }
            else if($target instanceof \Traversable)
            {
                $fn = function() use($target, $propName, $value) {
                    yield from $target;
                    yield $propName => $value;
                };
                $out = self::generatorToIterable($fn);
            }
            else if(is_object($target))
            {
                $out = clone $target;
                $out->$propName = $value;
            }
            return $out;
        });
        return $assoc(...$args);
    }
}