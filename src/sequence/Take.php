<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait Take
{
    // Because Take operates from the start of the sequence, for indexed arrays
    // (and sequences) the keys in the destination are the same as in the source
    // hence why takeWhileK and takeK are not required (whereas skipK and
    // skipWhileK are).
    
    /**
     * Transducer for the take function.
     * 
     * Returns a transducer that takes the specified number of elements from the
     * input, or less if the full amount are not available.
     * 
     * Once the quota has been met, the transducer signals completion using
     * 'Reduced'.
     * 
     * @param int $count    number of elements to take
     * 
     * @return callable
     */
    public static function takeT(int $count) : callable
    {
        $i = 0;
        return fn(callable $step) => function($acc, $v, $k) use($step, $count, &$i) {
            $i++;
            if($i < $count)
            {
                return $step($acc, $v, $k);
            }
            else if($i === $count)
            {
                return new Reduced($step($acc, $v, $k));
            }
            else
            {
                return new Reduced($acc);
            }
        };
    }

    /**
     * Takes the given number of elements from the sequence, or less if the
     * full number isn't available.
     * 
     * @param int $count                the number of elements to take
     * @param iterable|null $sequence   optional, the sequence to take from, threadable
     * @return type
     */
    public static function take(int $count, ?iterable $sequence = null)
    {
        if($sequence === null)
        {
            return fn(iterable $sequence) => self::take($count, $sequence);
        }
        
        // take preserves keys, so use K step
        return self::transduce(
            self::takeT($count),
            self::defaultStepK($sequence),
            self::emptied($sequence),
            $sequence
        );
    }
}