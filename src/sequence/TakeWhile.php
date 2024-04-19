<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait TakeWhile 
{
    // Because Take operates from the start of the sequence, for indexed arrays
    // (and sequences) the keys in the destination are the same as in the source
    // hence why takeWhileK and takeK are not required (whereas skipK and
    // skipWhileK are).
    
    /**
     * takeWhile transducer
     * 
     * @param callable $pred    predicate
     * 
     * @return callable transducer
     */
    public static function takeWhileT(callable $pred) : callable
    {
        return function($step) use($pred) {
            $fin = false;
            return function($acc, $v, $k) use(&$fin, $pred, $step) {
                $fin = $fin || !$pred($v, $k);
                if(!$fin)
                {
                    return $step($acc, $v, $k);
                }
                else
                {
                    return new Reduced($acc);
                }
            };
        };
    }
    
    /**
     * Create a collection containing all the values from the start of the
     * input collection up to the first value that does not satisfy the given
     * predicate.
     * 
     * @param callable $pred        predicate
     * @param iterable $sequence    optional, collection to read values from, threadable
     * 
     * @return iterable|callable new collection with only the taken values, or
     * a callable if $coll was null.
     */
    public static function takeWhile(callable $pred, ?iterable $sequence = null)
    {
        if($sequence === null)
        {
            return fn(iterable $sequence) => self::takeWhile($pred, $sequence);
        }
        // takeWhile preserves keys so use K step
        return self::transduce(
            self::takeWhileT($pred),
            self::defaultStepK($sequence),
            self::emptied($sequence),
            $sequence
        );
    }
}
