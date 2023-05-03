<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait Find
{
    /**
     * Find the first entry in a sequence that satisfies the predicate
     * 
     * @param callable $predicate       used to determine match (passed $v, $k)
     * @param object|iterable $target   thing to search for match in
     * 
     * @return variant the first value that satisfies the predicate, or null if 
     * no match can be found
     */
    public static function findFirst(callable $predicate, $target = null)
    {
        if($target === null)
        {
            return fn($target) => self::findFirst($predicate, $target);
        }
        if(is_object($target) && method_exists($target, "findFirst"))
        {
            return $target->findFirst($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? new Reduced($v) : $acc, null, $target);
        }
    }

    /**
     * Find the index of the first entry in a sequence that satisfies the 
     * predicate
     * 
     * @param callable $predicate       used to determine match (passed $v, $k)
     * @param object|iterable $target   thing to search for match in
     * 
     * @return int|string index of the first value that satisfies the predicate, or -1 
     * if no match can be found
     */
    public static function findFirstK(callable $predicate, $target = null)
    {
        if($target === null)
        {
            return fn($target) => self::findFirstK($predicate, $target);
        }
        if(is_object($target) && method_exists($target, "findFirstK"))
        {
            return $target->findFirstK($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? new Reduced($k) : $acc, -1, $target);
        }
    }
    
    /**
     * Find the last entry in a sequence that satisfies the predicate
     * 
     * @param callable $predicate       used to determine match (passed $v, $k)
     * @param object|iterable $target   thing to search for match in
     * 
     * @return variant the last value that satisfies the predicate, or null if 
     * no match can be found
     */
    public static function findLast(callable $predicate, $target = null)
    {
        if($target === null)
        {
            return fn($target) => self::findLast($predicate, $target);
        }
        if(is_object($target) && method_exists($target, "findLast"))
        {
            return $target->findLast($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? $v : $acc, null, $target);
        }
    }
    
    /**
     * Find the index of the last entry in a sequence that satisfies the 
     * predicate
     * 
     * @param callable $predicate       used to determine match (passed $v, $k)
     * @param object|iterable $target   thing to search for match in
     * 
     * @return int|string index of the last value that satisfies the predicate, or -1 
     * if no match can be found
     */
    public static function findLastK(callable $predicate, $target = null)
    {
        if($target === null)
        {
            return fn($target) => self::findLastK($predicate, $target);
        }
        if(is_object($target) && method_exists($target, "findLastK"))
        {
            return $target->findLastK($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? $k : $acc, null, $target);
        }
    }
}