<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait SkipWhile 
{
    /**
     * Transducer for skip-while functions
     *
     * Given a predicate, creates a new transducer that when called skips any 
     * leading values up until the first leading value that matches the given 
     * predicate.
     *
     * @param callable $pred        predicate function
     *
     * @return callable
     */
    public static function skipWhileT(callable $pred) : callable
    {
        $skipping = true;
        return fn(callable $step) => function($acc, $v, $k) use($pred, $step, &$skipping)
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
     * Returns a new collection that omits all leading items in the input collection
     * up until the first item to satisfy the given predicate.
     *
     * @param callable $pred            Test items in the input collection
     * @param iterable $sequence        Optional, input collection, threadable.
     *
     * @return iterable new collection with leading items that fail the predicate
     * removed.
     */
    public static function skipWhile(callable $pred, ?iterable $sequence = null)
    {
        if($sequence === null)
        {
            return fn(iterable $sequence) => self::skipWhile($pred, $sequence);
        }
        
        $out = self::transduce(
            self::skipWhileT($pred),
            self::defaultStep($sequence),
            self::emptied($sequence),
            $sequence
        );
        return $out;
    }

    /**
     * Returns a new collection that omits all leading items in the input collection
     * up until the first item to satisfy the given predicate.
     *
     * @param callable $pred            Test items in the input collection
     * @param iterable $sequence        Input collection
     *
     * @return iterable new collection with leading items that fail the predicate
     * removed. Retains keys from input collection.
     */
    public static function skipWhileK(callable $pred, ?iterable $sequence = null)
    {
        if($sequence === null)
        {
            return fn(iterable $sequence) => self::skipWhileK($pred, $sequence);
        }
        
        $out = self::transduce(
            self::skipWhileT($pred),
            self::defaultStepK($sequence),
            self::emptied($sequence),
            $sequence
        );
        return $out;
    }
}
