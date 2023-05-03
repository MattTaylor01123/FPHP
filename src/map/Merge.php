<?php

/*
 * (c) Matthew Taylor
 */

namespace src\map;

use InvalidArgumentException;

trait Merge
{
    /**
     * Merge multiple maps (objects or associative arrays) together.
     * 
     * Merging is performed from left to right. A new map is returned (the
     * inputs are not modified). The return type is the same type as the
     * leftmost map.
     * 
     * If no maps are provided then an empty stdClass is returned.
     * 
     * If the leftmost map is an object which implements the "mergeAllRight"
     * method then the remaining maps are passed as arguments to this method and
     * the result is returned as the output of this function.
     * 
     * @param array|object[] ...$maps   the maps to merge.
     *
     * @return mixed The new map resulting from the merge.
     * 
     * @throws InvalidArgumentException if any of the inputs are not objects or arrays
     */
    public static function mergeAllRight(...$maps)
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
        else if(is_object($maps[0]) && method_exists($maps[0], "mergeAllRight"))
        {
            $first = $maps[0];
            $rest = array_slice($maps, 1);
            $out = $first->mergeAllRight(...$rest);
        }
        else
        {
            $initial = self::emptied($maps[0]);
            $out = self::reduce(fn($acc, $map) =>
                self::reduce(fn($acc, $v, $k) => self::assoc($acc, $v, $k), $acc, $map), $initial, $maps);
        }
        return $out;
    }
    
    /**
     * Merge two maps (objects or associative arrays) together.
     * 
     * Merging is performed from left to right. A new map is returned (the
     * inputs are not modified). The return type is the same type as the
     * leftmost map.
     * 
     * If the leftmost map is an object which implements the "mergeRight"
     * method then the rightmost map is passed to this method and
     * the result is returned as the output of this function.
     * 
     * @param array|object      $map1    map to merge
     * @param array|object|null $map2   map to merge, threadable
     *
     * @return mixed The new map resulting from the merge. If
     * $map2 was null then callable.
     * 
     * @throws InvalidArgumentException if any of the inputs are not objects or arrays
     */
    public static function mergeRight($map1, $map2 = null)
    {
        if($map2 === null)
        {
            return fn($map2) => self::mergeRight($map1, $map2);
        }
        
        if(!self::all(fn($map) => is_array($map) || is_object($map), [$map1, $map2]))
        {
            throw new InvalidArgumentException("Every map must be an array or an object");
        }
        
        if(is_object($map1) && method_exists($map1, "mergeRight"))
        {
            $out = $map1->mergeRight($map2);
        }
        else
        {
            $initial = self::emptied($map1);
            $out = self::reduce(fn($acc, $map) =>
                self::reduce(fn($acc, $v, $k) => self::assoc($acc, $v, $k), $acc, $map), $initial, [$map1, $map2]);
        }

        return $out;
    }
    
    /**
     * Merge multiple maps (objects or associative arrays) together.
     * 
     * Merging is performed from right to left. A new map is returned (the
     * inputs are not modified). The return type is the same type as the
     * last map.
     * 
     * If no maps are provided then an empty stdClass is returned.
     * 
     * If the rightmost map is an object which implements the "mergeAllLeft"
     * method then the other maps are passed as arguments to this method and
     * the result is returned as the output of this function.
     * 
     * @param array|object[] ...$maps   the maps to merge.
     *
     * @return array|object The new map resulting from the merge.
     * 
     * @throws InvalidArgumentException if any of the inputs are not objects or arrays
     */
    public static function mergeAllLeft(...$maps)
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
        else if(is_object($maps[$noMaps - 1]) && method_exists($maps[$noMaps - 1], "mergeAllLeft"))
        {
            $first = $maps[$noMaps - 1];
            $rest = array_slice($maps, 0, -1);
            $out = $first->mergeAllLeft(...$rest);
        }
        else
        {
            $mapsRev = array_reverse($maps);
            $initial = self::emptied($mapsRev[0]);
            $out = self::reduce(fn($acc, $map) =>
                self::reduce(fn($acc, $v, $k) => self::assoc($acc, $v, $k), $acc, $map), $initial, $mapsRev);
        }
        return $out;
    }
    
    /**
     * Merge two maps (objects or associative arrays) together.
     * 
     * Merging is performed from right to left. A new map is returned (the
     * inputs are not modified). The return type is the same type as the
     * rightmost map.
     * 
     * If the rightmost map is an object which implements the "mergeLeft"
     * method then the leftmost map is passed to this method and
     * the result is returned as the output of this function.
     * 
     * @param array|object      $map1    map to merge
     * @param array|object|null $map2   map to merge, threadable
     *
     * @return mixed The new map resulting from the merge. If
     * $map2 was null then callable.
     * 
     * @throws InvalidArgumentException if any of the inputs are not objects or arrays
     */
    public static function mergeLeft($map1, $map2 = null)
    {
        if($map2 === null)
        {
            return fn($map2) => self::mergeLeft($map1, $map2);
        }
        
        if(!self::all(fn($map) => is_array($map) || is_object($map), [$map1, $map2]))
        {
            throw new InvalidArgumentException("Every map must be an array or an object");
        }
        
        if(is_object($map2) && method_exists($map2, "mergeLeft"))
        {
            $out = $map2->mergeLeft($map1);
        }
        else
        {
            $initial = self::emptied($map2);
            $out = self::reduce(fn($acc, $map) =>
                self::reduce(fn($acc, $v, $k) => self::assoc($acc, $v, $k), $acc, $map), $initial, [$map2, $map1]);
        }
        return $out;
    }
}