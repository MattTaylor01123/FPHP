<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

use InvalidArgumentException;

trait Assoc
{
    /**
     * Returns a new map containing all the key->value pairs in the input
     * map plus the key->value pair defined by the other parameters.
     *
     * @param array|object $map     input map
     * @param mixed $val            value to add to map
     * @param mixed $key            key to use when adding value to map
     *
     * @return array|object    new map (type matches $map input)
     *
     * @throws InvalidArgumentException if input map is not of type array or
     * object.
     */
    public static function assoc($map, $val, $key)
    {
        if(is_object($map) && method_exists($map, "assoc"))
        {
            return $map->assoc($val, $key);
        }
        if(is_array($map))
        {
            $out = $map;
            $out[$key] = $val;
        }
        else if(is_object($map))
        {
            $out = clone $map;
            $out->$key = $val;
        }
        else
        {
            throw new InvalidArgumentException("'map' must be of type array or object");
        }
        return $out;
    }
}