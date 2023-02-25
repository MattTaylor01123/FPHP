<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

trait Evolve
{
    /**
     * Creates a new object by applying a series of transformations to a given
     * object's properties.
     *
     * Transformations are specified using an associative array indexed by
     * object property name. The values of the associative array are
     * transformation functions which are passed the original value of the
     * property.
     *
     * Also works for associative arrays.
     *
     * @param array $spec               The transformations to perform.
     * @param array|object $target      The base object to transform - threadable.
     * @return array|object|callable    Same type as $target, or a callable if
     *                                  $target was null.
     */
    public static function evolve(array $spec, $target = null)
    {
        if($target === null)
        {
            return fn($target) => self::evolve($spec, $target);
        }
        if(!is_array($target) && !is_object($target))
        {
            throw new InvalidArgumentException("'target' must be associative array or object");
        }

        $out = $target;
        foreach($spec as $field => $fn)
        {
            if(self::hasProp($field, $out))
            {
                $curr = self::prop($field, $target);
                $out = self::assoc($out, ($fn)($curr), $field);
            }
        }
        return $out;
    }
}