<?php

/**
 * (c) Matthew Taylor
 */
namespace src\sequence;

trait Split 
{
    /**
     * Transducer for split
     * 
     * @param int $length           Length of each sequence
     * @param int $offset           Gap between sequences. 0 = no gap, > 0 is a
     *                              gap of that many elements, and < 0 results
     *                              in overlapping sequences
     * 
     * @return callable             transducer function
     * 
     * @throws InvalidArgumentException if length <= 0 or offset <= -length
     */
    public static function splitT(int $length, int $offset) : callable
    {
        if($length <= 0)
        {
            throw new InvalidArgumentException("Invalid length");
        }
        if($offset < 0 && $offset * -1 >= $length)
        {
            throw new InvalidArgumentException("Invalid offset");
        }
        
        $index = 0;
        $cache = [];
        $posOffset = max($offset, 0);
        $cacheLength = 0;
        return fn($step) => function($acc, $v) use(&$index, &$cache, $length, $offset, &$cacheLength, $step, $posOffset) {
            
            // split the sequence into segments, where the length of the segment 
            // is the length + any positive offset
            $pos = ($index % ($length + $posOffset));
            
            // if the current element is in the length part of the segment, rather
            // than in the positive offset part, then include it in the cache
            if($pos < $length)
            {
                $cache[] = $v;
                $cacheLength = $cacheLength + 1;
            }
            
            // if the cache length has reached the required length then the assembled
            // segment needs to be returned
            if($cacheLength === $length)
            {
                // make a copy of the cache value to return, prior to clearing
                // the cache
                $compSeg = $cache;
                
                // if the offset is < 0 then we have overlapping segments.
                // take the overlap portion of the next segment from the current
                // one and use it to seed the cache
                if($offset < 0)
                {
                    $cache = array_slice($cache, $offset);
                    $cacheLength = abs($offset);
                }
                // otherwise no overlap, no reuse of values, start with empty
                // cache
                else
                {
                    $cache = [];
                    $cacheLength = 0;
                }

                $out = $step($acc, $compSeg);
            }
            else
            {
                $out = $acc;
            }

            $index = $index + 1;
            return $out;
        };
    }
    
    /**
     * Given an input sequence, creates multiple sub-sequences.
     * 
     * @param int $length           Length of each sequence
     * @param int $offset           Gap between sequences. 0 = no gap, > 0 is a
     *                              gap of that many elements, and < 0 results
     *                              in overlapping sequences
     * @param iterable $sequence    The sequence to create sub sequences from.
     *                              Threadable
     * 
     * @return iterable|callable    Same type as input sequence, or callable if
     *                              input sequence is null
     */
    public static function split(int $length, int $offset, ?iterable $sequence = null)
    {
        if($sequence === null)
        {
            return fn($sequence) => self::split($length, $offset, $sequence);
        }
        $out = self::transduce(
            self::splitT($length, $offset),
            self::defaultStep($sequence),
            self::emptied($sequence),
            $sequence
        );
        return $out;
    }
}
