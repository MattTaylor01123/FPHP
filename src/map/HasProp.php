<?php

/*
 * (c) Matthew Taylor
 */

namespace src\map;

trait HasProp
{
    /**
     * Checks if a map contains the given property
     * 
     * @param string        $propName   property to check for
     * @param array|object  $map        map to check in, threadable
     * 
     * @return bool|callable true if the map contains a mapping for the 
     * property, false otherwise. If $map is threaded then callable.
     */
    public static function hasProp(string $propName, $map = null)
    {
        if($map === null)
        {
            return fn($map) => self::hasProp($propName, $map);
        }
        
        return ((is_object($map) && property_exists($map, $propName)) ||
                (is_array($map) && key_exists($propName, $map)));
    }
}