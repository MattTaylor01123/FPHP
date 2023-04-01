<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

use InvalidArgumentException;

trait Merge
{
    /**
     * Merge multiple maps (objects or associative arrays) together.
     * 
     * Merging is performed from left to right. A new map is returned (the
     * inputs are not modified). The return type is the same type as the
     * first map.
     * 
     * If no maps are provided then an empty stdClass is returned.
     * 
     * @param array|object[] ...$maps   the maps to merge.
     *
     * @return object|array The new map resulting from the merge.
     */
    public static function merge(...$maps)
    {
        if(!self::all(fn($map) => is_array($map) || is_object($map), $maps))
        {
            throw new InvalidArgumentException("Every map must be an array or an object");
        }
        
        $noMaps = count($maps);
        if($noMaps === 0)
        {
            $out = new \stdClass();
        }
        else if(is_object($maps[0]) && method_exists($maps[0], "merge"))
        {
            $first = $maps[0];
            $rest = array_values(array_filter($maps, fn($k) => $k > 0, ARRAY_FILTER_USE_KEY ));
            $out = $first->merge(...$rest);
        }
        else
        {
            $initial = self::emptied($maps[0]);
            $out = self::reduce(fn($acc, $map) =>
                self::reduce(fn($acc, $v, $k) => self::assoc($acc, $v, $k), $acc, $map), $initial, $maps);
        }
        return $out;
    }
}