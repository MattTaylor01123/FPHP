<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

use InvalidArgumentException;

trait Map
{
    /**
     * map transducer
     * 
     * @param callable $func    transform function
     * 
     * @return callable transducer
     */
    public static function mapT(callable $func) : callable
    {
        return fn($step) => fn($acc, $v, $k) => $step($acc, $func($v, $k), $k);
    }

    public static function map(callable $func, $coll)
    {
        if(is_object($coll) && method_exists($coll, "map"))
        {
            $out = $coll->map($func);
        }
        // array_map callback doesn't support keys
        else if( is_object($coll) || is_array($coll) || self::isTraversable($coll) || self::isGenerator($coll))
        {
            // map preserves keys, so use K step
            $out = self::transduce(
                self::mapT($func),
                self::defaultStepK($coll),
                self::emptied($coll),
                $coll
            );
        }
        else
        {
            throw new InvalidArgumentException("target must be one of array, stdClass, generator, functor.");
        }
        return $out;
    }
}