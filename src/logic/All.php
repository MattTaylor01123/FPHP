<?php

/*
 * (c) Matthew Taylor
 */

namespace src\logic;

use src\collection\Reduced;

trait All
{
    /**
     * Submit every value in an iterable to a predicate test. If the predicate
     * returns True for any value then this function returns True, otherwise
     * False.
     * 
     * @param callable $fnPred          predicate
     * @param iterable|null $sequence   values to check, threadable
     * 
     * @return bool True if any values match the predicate, false otherwise. If
     * $sequence is null then returns a callable.
     */
    public static function all(callable $fnPred, ?iterable $sequence = null)
    {
        if(is_null($sequence))
        {
            return fn(iterable $sequence) => self::all($fnPred, $sequence);
        }
        if(is_object($sequence) && method_exists($sequence, "all"))
        {
            return $sequence->all($fnPred);
        }
        
        return self::reduce(fn($acc, $v, $k) =>
            (!$fnPred($v, $k) ? new Reduced(false) : true), true, $sequence);
    }
}