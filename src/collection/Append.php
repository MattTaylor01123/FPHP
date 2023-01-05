<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

use InvalidArgumentException;

trait Append 
{
    /**
     * Creates a new un-keyed collection which contains all the values from the
     * input collection and then the passed in value appended as the last value
     * in the new collection.
     *
     * @param iterable $acc    input collection
     * @param mixed $val       value to append to end of new collection
     *
     * @return iterable new collection
     *
     * @throws InvalidArgumentException if input collection is not an array or a
     * traversable.
     */
    public static function append(iterable $acc, $val) : iterable
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
