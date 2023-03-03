<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

trait Group
{
    /**
     * Produces an associative array
     * - keys: generated by passing each value in the input through a mapping function
     * - values: array containing all the values in the input which have the same group
     *
     * @param callable $fnGroup     given input value, derive group
     * @param iterable $coll        optional source collection - threadable
     * 
     * @return array|callable grouped collection, or callable if $coll is omitted.
     */
    public static function groupBy(callable $fnGroup, ?iterable $coll = null)
    {
        if($coll === null)
        {
            return fn(iterable $coll) => self::groupBy($fnGroup, $coll);
        }
        
        return self::groupReduceBy(
            $fnGroup,
            fn($acc, $v) => self::append($acc, $v),
            [],
            $coll
        );
    }

    /**
     * Produces an associative array
     * - keys: generated by passing each value in the input through a mapping function
     * - values: array of all the values in the input with the same group, after being
     * passed through the map function
     *
     * @param callable $fnGroup     given input value, derive group
     * @param callable $fnMap       given input value, map to output value
     * @param iterable $coll        optional source collection - threadable
     * 
     * @return array|callable grouped collection, or callable if $coll is omitted.
     */
    public static function groupMapBy(callable $fnGroup, callable $fnMap, ?iterable $coll = null)
    {
        if($coll === null)
        {
            return fn(iterable $coll) => self::groupMapBy($fnGroup, $fnMap, $coll);
        }
        
        return self::groupReduceBy(
            $fnGroup,
            fn($acc, $v, $k) => self::append($acc, $fnMap($v, $k)),
            [],
            $coll
        );
    }

    /**
     * Produces an associative array
     * - keys: generated by passing each value in the input through a mapping function
     * - values: generated by passing each value in the input that is in the same group
     * (as per the mapping function) through a reducing function.
     *
     * @param callable $fnGroup     given input value, derive group
     * @param callable $fnReduce    given accumulator and input value, derive new accumulated value
     * @param mixed $initial        starting value for each reduction
     * @param iterable $coll        optional source collection - threadable
     *
     * @return array|callable grouped collection, or callable if $coll is omitted.
     */
    public static function groupReduceBy(callable $fnGroup, callable $fnReduce, $initial, ?iterable $coll = null)
    {
        if($coll === null)
        {
            return fn(iterable $coll) => self::groupReduceBy($fnGroup, $fnReduce, $initial, $coll);
        }
        
        $out = array();
        foreach($coll as $k => $v)
        {
            $g = $fnGroup($v, $k);
            if($g === null)
            {
                continue;
            }
            if(!array_key_exists($g, $out))
            {
                $out[$g] = self::emptied($initial);
            }
            $out[$g] = $fnReduce($out[$g], $v, $k);
        }
        return $out;
    }
}