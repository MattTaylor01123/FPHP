<?php

/*
 * (c) Matthew Taylor
 */

namespace src\map;

use InvalidArgumentException;

trait Dissoc
{
    /**
     * Removes a key->value pair from a map.
     * 
     * @param object|array $map     input map
     * @param mixed $propName       key to remove from map
     * 
     * @return object|array     new map containing everything key from the
     * source map except the key to be removed.
     * 
     * @throws InvalidArgumentException if map is not an array or an object
     */
    public static function dissoc($map, $propName)
    {
        if(is_array($map))
        {
            $out = $map;
            unset($out[$propName]);
        }
        else if(is_object($map))
        {
            $out = clone $map;
            unset($out->$propName);
        }
        else
        {
            throw new InvalidArgumentException("'map' must be of type array or object");
        }
        return $out;
    }
}