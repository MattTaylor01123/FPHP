<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

use InvalidArgumentException;

trait Assoc
{
    /**
     * Returns a new keyed collection containing all the values in the input
     * collection plus the value passed in as the final value at the end of the
     * collection, keyed with the key value passed in
     *
     * @param array|iterable|object $acc    input collection
     * @param mixed $val                    value to append at end of new collection
     * @param mixed $key                    key to use when appending value to end of new collection
     *
     * @return array|iterable|object    new collection
     *
     * @throws InvalidArgumentException if input collection is not of type array, iterable, or
     * object.
     */
    public static function assoc($acc, $val, $key)
    {
        if(is_object($acc) && method_exists($acc, "assoc"))
        {
            return $acc->assoc($val, $key);
        }
        if(is_array($acc))
        {
            $out = $acc;
            $out[$key] = $val;
        }
        else if(self::isTraversable($acc) || self::isGenerator($acc))
        {
            $returnedVal = false;
            $fn = function() use($key, $val, $acc, &$returnedVal) {
                foreach($acc as $k => $v)
                {
                    if($k === $key)
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
                    yield $key => $val;
                }
            };
            $out = self::generatorToIterable($fn);
        }
        else if(is_object($acc))
        {
            $out = clone $acc;
            $out->$key = $val;
        }
        else
        {
            throw new InvalidArgumentException("'acc' must be of type array, traversable, or object");
        }
        return $out;
    }
}