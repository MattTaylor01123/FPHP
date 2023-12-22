<?php

/*
 * (c) Matthew Taylor
 */

namespace src\logic;

trait AllPass 
{
    /**
     * Creates a predicate function by combining other predicate functions.
     * The new predicate function takes 0 or more arguments which are all
     * passed to the individual predicate functions.
     * 
     * @param callable $preds       the predicates to combine
     * 
     * @return callable the combined predicate
     */
    public static function allPass(callable ...$preds) : callable
    {
        return fn(...$vals) => self::all(fn($pred) => $pred(...$vals), $preds);
    }
}
