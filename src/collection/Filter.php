<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

use InvalidArgumentException;

trait Filter
{
    /**
     * filter transducer - returns a step function which only passes through
     * passed-in values that satisfy the predicate function.
     *
     * Keys in the input values will be retained or ignored, depending on the
     * step function provided.
     *
     * @param callable $predicate       test applied to each value passed in
     * @param callable $step            passed every input value that satisfies
     *                                  the predicate
     *
     * @return callable new step function that applies the filter when called
     */
    public static function filterT(callable $predicate, callable $step) : callable
    {
        return fn($acc, $v, $k) => ($predicate($v, $k) ? $step($acc, $v, $k) : $acc);
    }

    /**
     * Keeps only those values in the target that match the given predicate
     * function. This filter function retains keys in the target collection.
     *
     * @param callable $predicate       test applied to each value in $target
     * @param mixed $target             collection to filter
     *
     * @return mixed a new collection containing only those values in target
     * that satisfy the given predicate function.
     *
     * @throws InvalidArgumentException if target is not an array, object,
     * traversable, or generator.
     */
    public static function filterK(callable $predicate, mixed $target) : mixed
    {
        if (is_array($target))
        {
            $out = array_filter($target, $predicate, ARRAY_FILTER_USE_BOTH );
        }
        else if(is_object($target) || ($target instanceof \Traversable) || self::isGenerator($target))
        {
            // transduce but passing assoc as step function, so that key is preserved
            $out = self::transduce(
                fn($step) => self::filterT($predicate, $step),
                fn($acc, $v, $k) => self::assoc($acc, $v, $k),
                self::emptied($target),
                $target
            );
        }
        else
        {
            throw new InvalidArgumentException(
                "'target' must be one of array, traversable, object, or generator"
            );
        }
        return $out;
    }

    /**
     * Keeps only those values in the target that match the given predicate
     * function. This filter function ignores keys in the target collection.
     *
     * @param callable $predicate       test applied to each value in $target
     * @param mixed $target             collection to filter
     *
     * @return mixed a new collection containing only those values in target
     * that satisfy the given predicate function.
     *
     * @throws InvalidArgumentException if target is not an array, object,
     * traversable, or generator.
     */
    public static function filter(callable $predicate, mixed $target) : mixed
    {
        if(is_array($target))
        {
            $out = array_values(array_filter($target, $predicate, ARRAY_FILTER_USE_BOTH));
        }
        else if(is_object($target) || ($target instanceof \Traversable) || self::isGenerator($target))
        {
            $notTravOrGen = !($target instanceof \Traversable || self::isGenerator($target));
            // use the transduce filter, but ignore key
            $out = self::transduce(
                fn($step) => self::filterT($predicate, $step),
                fn($acc, $v) => self::append($acc, $v),
                $notTravOrGen ? [] : self::emptied($target),
                $target
            );
        }
        else
        {
            throw new InvalidArgumentException(
                "'target' must be one of array, traversable, object, or generator"
            );
        }
        return $out;
    }
}