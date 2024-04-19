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
     * Creates a new transducer which when called skips over the first $count
     * values, only passing every value after that to the passed in step 
     * function.
     *
     * @param int $count        Number of items to skip
     * 
     * @return callable
     */
    public static function skipT(int $count) : callable
    {
        if($count < 0)
        {
            throw new InvalidArgumentException("'count' cannot be negative");
        }
        $skipped = 0;
        return fn(callable $step) => function($acc, $v, $k) use($count, $step, &$skipped)
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
     * Creates and returns a collection of the same type as the input but with the
     * first $count items removed.
     *
     * @param int $count          Number of items to skip.
     * @param iterable $sequence  Optional, collection whose starting items will be skipped, threadable.
     *
     * @return iterable new collection with leading $count items removed.
     */
    public static function skip(int $count, ?iterable $sequence = null)
    {
        if($count < 0)
        {
            throw new InvalidArgumentException("'count' cannot be negative");
        }
        if($sequence === null)
        {
            return fn(iterable $sequence) => self::skip($count, $sequence);
        }
        else if(is_array($sequence))
        {
            $out = array_values(array_slice($sequence, $count));
        }
        else
        {
            $out = self::transduce(
                self::skipT($count),
                self::defaultStep($sequence),
                self::emptied($sequence),
                $sequence
            );
        }
        return $out;
    }

    /**
     * Creates and returns a collection of the same type as the input but with the
     * first $count items removed.
     *
     * @param int $count            Number of items to skip.
     * @param iterable $sequence    Optional, collection whose starting items will be skipped, threadable.
     *
     * @return iterable new collection with leading $count items removed. Retains
     * keys from input collection.
     */
    public static function skipK(int $count, ?iterable $sequence = null)
    {
        if($count < 0)
        {
            throw new InvalidArgumentException("'count' cannot be negative");
        }
        if($sequence === null)
        {
            return fn(iterable $sequence) => self::skipK($count, $sequence);
        }
        if(is_array($sequence))
        {
            $out = array_slice($sequence, $count);
        }
        else
        {
            $out = self::transduce(
                self::skipT($count),
                self::defaultStepK($sequence),
                self::emptied($sequence),
                $sequence
            );
        }
        return $out;
    }
}