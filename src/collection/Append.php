<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

use InvalidArgumentException;
use Traversable;

trait Append 
{
    /**
     * Creates a new keyed collection which contains all the values from the input
     * collection and then the passed in key => value pair appended to the end.
     *
     * Regardless of acc's type, the returned value will always be a lazy
     * Traversable. Otherwise, for arrays, if the key already existed in the array
     * then the new value would overwrite the old value rather than being appended
     * to the end.
     *
     * I.e. keys are not guaranteed to be unique in the returned Traversable.
     *
     * @param iterable $acc     input collection
     * @param mixed $val        value to append to end of collection
     * @param mixed $key        key to associate with value
     *
     * @return Traversable new collection
     *
     * @throws InvalidArgumentException if input collection is not an array or a
     * traversable.
     */
    public static function appendK(iterable $acc, $val, $key) : Traversable
    {
        if(is_array($acc) || self::isTraversable($acc) || self::isGenerator($acc))
        {
            $fn = function() use($val, $key, $acc) {
                yield from $acc;
                yield $key => $val;
            };
            $out = self::generatorToIterable($fn);
        }
        else
        {
            throw new InvalidArgumentException("'acc' must be of type array or traversable");
        }
        return $out;
    }

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
            $out = array_values($acc);
            $out[] = $val;
        }
        else if(self::isTraversable($acc) || self::isGenerator($acc))
        {
            $fn = function() use($val, $acc) {
                // don't yield from as not preserving keys
                foreach($acc as $v)
                {
                    yield $v;
                }
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
