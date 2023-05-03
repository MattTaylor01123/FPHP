<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

use InvalidArgumentException;

trait Skip
{
    /**
     * Transducer for the skip functions.
     *
     * Creates a new step function which when called skips over the first $count
     * values, only passing every value after that to the passed in step function.
     *
     * @param int $count        Number of items to skip
     * @param callable $step    Step function
     * 
     * @return callable
     */
    public static function skipT(int $count, callable $step) : callable
    {
        if($count < 0)
        {
            throw new InvalidArgumentException("'count' cannot be negative");
        }
        $skipped = 0;
        return function($acc, $v, $k) use($count, $step, &$skipped)
        {
            if($skipped < $count)
            {
                $skipped++;
                return $acc;
            }
            else
            {
                return $step($acc, $v, $k);
            }
        };
    }

    /**
     * Transducer for skip-while functions
     *
     * Given a predicate and a step function, creates a new step function
     * that when called skips any leading values up until the first leading
     * value that matches the given predicate.
     *
     * @param callable $pred        predicate function
     * @param callable $step        step function
     *
     * @return callable
     */
    public static function skipWhileT(callable $pred, callable $step) : callable
    {
        $skipping = true;
        return function($acc, $v, $k) use($pred, $step, &$skipping)
        {
            $skipping = $skipping && $pred($v, $k);
            if(!$skipping)
            {
                return $step($acc, $v, $k);
            }
            else
            {
                return $acc;
            }
        };
    }

    /**
     * Creates and returns a collection of the same type as the input but with the
     * first $count items removed.
     *
     * @param int $count            Number of items to skip.
     * @param iterable $collection  Collection whose starting items will be skipped.
     *
     * @return iterable new collection with leading $count items removed.
     */
    public static function skip(int $count, iterable $collection) : iterable
    {
        if($count < 0)
        {
            throw new InvalidArgumentException("'count' cannot be negative");
        }
        if(is_array($collection))
        {
            $out = array_values(array_slice($collection, $count));
        }
        else
        {
            $out = self::transduce(
                fn($step) => self::skipT($count, $step),
                self::defaultStep($collection),
                self::emptied($collection),
                $collection
            );
        }
        return $out;
    }

    /**
     * Creates and returns a collection of the same type as the input but with the
     * first $count items removed.
     *
     * @param int $count            Number of items to skip.
     * @param iterable $collection  Collection whose starting items will be skipped.
     *
     * @return iterable new collection with leading $count items removed. Retains
     * keys from input collection.
     */
    public static function skipK(int $count, iterable $collection) : iterable
    {
        if($count < 0)
        {
            throw new InvalidArgumentException("'count' cannot be negative");
        }
        if(is_array($collection))
        {
            $out = array_slice($collection, $count);
        }
        else
        {
            $out = self::transduce(
                fn($step) => self::skipT($count, $step),
                self::defaultStepK($collection),
                self::emptied($collection),
                $collection
            );
        }
        return $out;
    }

    /**
     * Returns a new collection that omits all leading items in the input collection
     * up until the first item to satisfy the given predicate.
     *
     * @param callable $pred            Test items in the input collection
     * @param iterable $collection      Input collection
     *
     * @return iterable new collection with leading items that fail the predicate
     * removed.
     */
    public static function skipWhile(callable $pred, iterable $collection) : iterable
    {
        $out = self::transduce(
            fn($step) => self::skipWhileT($pred, $step),
            self::defaultStep($collection),
            self::emptied($collection),
            $collection
        );
        return $out;
    }

    /**
     * Returns a new collection that omits all leading items in the input collection
     * up until the first item to satisfy the given predicate.
     *
     * @param callable $pred            Test items in the input collection
     * @param iterable $collection      Input collection
     *
     * @return iterable new collection with leading items that fail the predicate
     * removed. Retains keys from input collection.
     */
    public static function skipWhileK(callable $pred, iterable $collection) : iterable
    {
        $out = self::transduce(
            fn($step) => self::skipWhileT($pred, $step),
            self::defaultStepK($collection),
            self::emptied($collection),
            $collection
        );
        return $out;
    }
}