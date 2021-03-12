<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

use InvalidArgumentException;

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
                $returnedVal = false;
                $fn = function() use($propName, $val, $acc, &$returnedVal) {
                    foreach($acc as $k => $v)
                    {
                        if($k === $propName)
                        {
                            $returnedVal = true;
                            yield $k => $val;
                        }
                        else
                        {
                            yield $k => $v;
                        }
                    }
                    if(!$returnedVal)
                    {
                        $returnedVal = true;
                        yield $propName => $val;
                    }
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