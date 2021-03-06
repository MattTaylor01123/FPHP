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
        $assoc = self::curry(function($acc, $val, $propName) {
            if(is_array($acc))
            {
                $out = $acc;
                $out[$propName] = $val;
            }
            else if($acc instanceof \Traversable)
            {
                $fn = function() use($acc, $propName, $val) {
                    yield from $acc;
                    yield $propName => $val;
                };
                $out = self::generatorToIterable($fn);
            }
            else if(is_object($acc))
            {
                $out = clone $acc;
                $out->$propName = $val;
            }
            else
            {
                throw new InvalidArgumentException("'acc' must be of type array, traversable, or object");
            }
            return $out;
        });
        return $assoc(...$args);
    }
}