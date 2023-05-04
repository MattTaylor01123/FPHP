<?php

/*
 * (c) Matthew Taylor
 */

namespace src\map;

trait HasProps
{
    /**
     * Returns true if the given map has all of the given properties, false
     * otherwise.
     * 
     * @param array $propNames      properties to check for
     * @param array|object $map     map to check in for properties, threadable
     * 
     * @return bool|callable    True if all properties are present in map,
     * false otherwise. Callable if $map is null.
     */
    public static function hasProps(array $propNames, $map = null)
    {
        if($map === null)
        {
            return fn($map) => self::hasProps($propNames, $map);
        }
        return self::all(fn($p) => self::hasProp($p, $map), $propNames);
    }
}