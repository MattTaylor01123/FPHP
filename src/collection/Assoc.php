<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

use InvalidArgumentException;
use Traversable;

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
            else if(self::isTraversable($acc) || self::isGenerator($acc))
            {
                $fn = function() use($propName, $val) {
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