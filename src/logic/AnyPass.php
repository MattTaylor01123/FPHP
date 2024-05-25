<?php

/*
 * (c) Matthew Taylor
 */

namespace src\logic;

trait AnyPass 
{
    /**
     * Takes one or multiple predicate functions and returns a new predicate
     * function which takes one or more arguments and returns true if any of 
     * the predicates returns true. Short circuits.
     * 
     * @param callable $predicates
     * 
     * @return callable
     */
    public static function anyPass(callable ...$predicates) : callable
    {
        return fn(...$vals) => self::any(fn($fn) => $fn(...$vals), $predicates);
    }
}
