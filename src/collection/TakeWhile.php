<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

trait TakeWhile 
{
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
     * @param callable $pred    predicate
     * @param iterable $coll    collection to read values from, threadable
     * 
     * @return iterable|callable new collection with only the taken values, or
     * a callable if $coll was null.
     */
    public static function takeWhile(callable $pred, ?iterable $coll = null)
    {
        if($coll === null)
        {
            return fn(iterable $coll) => self::takeWhile($pred, $coll);
        }
        // takeWhile preserves keys so use K step
        return self::transduce(
            self::takeWhileT($pred),
            self::defaultStepK($coll),
            self::emptied($coll),
            $coll
        );
    }
}
