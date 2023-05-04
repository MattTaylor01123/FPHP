<?php

/*
 * (c) Matthew Taylor
 */

namespace src\map;

trait Evolve
{
    /**
     * Creates a new map by applying a series of transformations to a given
     * map's properties.
     *
     * Transformations are specified using an associative array indexed by
     * map property name. The values of the associative array are
     * transformation functions which are passed the original value of the
     * property.
     *
     * @param array $spec               The transformations to perform.
     * @param array|object $map         The base map to transform - threadable.
     * @return array|object|callable    Same type as $map, or a callable if
     *                                  $map was null.
     */
    public static function evolve(array $spec, $map = null)
    {
        if($map === null)
        {
            return fn($map) => self::evolve($spec, $map);
        }
        if(!is_array($map) && !is_object($map))
        {
            throw new InvalidArgumentException("'map' must be associative array or object");
        }

        $out = $map;
        foreach($spec as $field => $fn)
        {
            if(self::hasProp($field, $out))
            {
                $curr = self::prop($field, $map);
                $out = self::assoc($out, ($fn)($curr), $field);
            }
        }
        return $out;
    }
}