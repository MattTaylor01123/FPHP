<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait Project
{
    /**
     * Like SQL's 'select' but for iterables of maps instead of tables of rows.
     * Maps an iterable of maps picking only the given properties from the maps.
     * If a property does not exist in a map then it is ignored (and is not
     * present in the final map).
     * 
     * @param array $properties     only these properties will be included in
     *                              the output maps
     * @param iterable $coll        an iterable of maps, threadable
     * 
     * @return iterable|callable    an iterable of maps. If $coll was null
     * then callable.
     */
    public static function project(array $properties, ?iterable $coll = null)
    {
        if(is_null($coll))
        {
            return fn($coll) => self::project($properties, $coll);
        }
        if(is_object($coll) && method_exists($coll, "project"))
        {
            $out = $coll->project($properties);
        }
        else
        {
            $out = self::map(fn($v) => self::pick($properties, $v), $coll);
        }
        return $out;
    }
}