<?php

/*
 * (c) Matthew Taylor
 */

namespace src\map;

class AssocPath 
{
    /**
     * Associates a value in a nested structure. If any levels do not exist
     * then either an array or an stdClass is created, depending on the path
     * value - integers = array, strings = stdClass.
     * 
     * @param string|int[] $path    where in the structure the value shall be set
     * @param mixed $value          the value to set
     * @param object|array $map     the nested structure, threadable
     * 
     * @return array|object|callable    The modified nested structure. Same type 
     *                                  as $map, or a callable if $map was null.
     */
    public static function assocPath(array $path, $value, $map = null)
    {
        if($map === null)
        {
            return fn($map) => self::assocPath($path, $value, $map);
        }
        
        $len = count($path);
        if($len === 0)
        {
            return $map;
        }
        else if($len === 1)
        {
            return self::assoc($map, $value, $path[0]);
        }
        else if(self::hasProp($path[0], $map))
        {
            $subColl = self::prop($path[0], $map);
            $newSubColl = self::assocPath(array_slice($path, 1), $value, $subColl);
            return self::assoc($map, $newSubColl, $path[0]);
        }
        else
        {
            $subColl = is_int($path[1]) ? [] : new stdClass();
            $newSubColl = self::assocPath(array_slice($path, 1), $value, $subColl);
            $out = self::assoc($map, $newSubColl, $path[0]);
            return $out;
        }
    }
}
