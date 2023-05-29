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
     * @param callable $predicate           used to determine match (passed $v, $k)
     * @param object|iterable $sequence     thing to search for match in
     * 
     * @return variant the first value that satisfies the predicate, or null if 
     * no match can be found
     */
    public static function findFirst(callable $predicate, $sequence = null)
    {
        if($sequence === null)
        {
            return fn($sequence) => self::findFirst($predicate, $sequence);
        }
        if(is_object($sequence) && method_exists($sequence, "findFirst"))
        {
            return $sequence->findFirst($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? new Reduced($v) : $acc, null, $sequence);
        }
    }

    /**
     * Find the index of the first entry in a sequence that satisfies the 
     * predicate
     * 
     * @param callable $predicate           used to determine match (passed $v, $k)
     * @param object|iterable $sequence     thing to search for match in
     * 
     * @return int|string index of the first value that satisfies the predicate, or -1 
     * if no match can be found
     */
    public static function findFirstIndex(callable $predicate, $sequence = null)
    {
        if($sequence === null)
        {
            return fn($sequence) => self::findFirstIndex($predicate, $sequence);
        }
        if(is_object($sequence) && method_exists($sequence, "findFirstIndex"))
        {
            return $sequence->findFirstIndex($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? new Reduced($k) : $acc, -1, $sequence);
        }
    }
    
    /**
     * Find the last entry in a sequence that satisfies the predicate
     * 
     * @param callable $predicate           used to determine match (passed $v, $k)
     * @param object|iterable $sequence     thing to search for match in
     * 
     * @return variant the last value that satisfies the predicate, or null if 
     * no match can be found
     */
    public static function findLast(callable $predicate, $sequence = null)
    {
        if($sequence === null)
        {
            return fn($sequence) => self::findLast($predicate, $sequence);
        }
        if(is_object($sequence) && method_exists($sequence, "findLast"))
        {
            return $sequence->findLast($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? $v : $acc, null, $sequence);
        }
    }
    
    /**
     * Find the index of the last entry in a sequence that satisfies the 
     * predicate
     * 
     * @param callable $predicate           used to determine match (passed $v, $k)
     * @param object|iterable $sequence     thing to search for match in
     * 
     * @return int|string index of the last value that satisfies the predicate, or -1 
     * if no match can be found
     */
    public static function findLastIndex(callable $predicate, $sequence = null)
    {
        if($sequence === null)
        {
            return fn($sequence) => self::findLastIndex($predicate, $sequence);
        }
        if(is_object($sequence) && method_exists($sequence, "findLastIndex"))
        {
            return $sequence->findLastIndex($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? $k : $acc, -1, $sequence);
        }
    }
}