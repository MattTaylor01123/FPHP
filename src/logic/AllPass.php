<?php

/*
 * (c) Matthew Taylor
 */

namespace src\logic;

trait AllPass 
{
    /**
     * Takes one or multiple predicate functions and returns a new predicate
     * function which takes one or more arguments and returns true if all of 
     * the predicates returns true. Short circuits.
     * 
     * @param callable $predicates
     * 
     * @return callable
     */
    public static function allPass(callable ...$predicates) : callable
    {
        return fn(...$vals) => self::all(fn($pred) => $pred(...$vals), $predicates);
    }
}
