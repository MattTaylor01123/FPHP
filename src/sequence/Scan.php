<?php

/**
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait Scan 
{
    /**
     * Transducer for scan
     * 
     * @param callable $fnTrans         transformation function (prev, v, k)
     * @param mixed $initial            the value for prev for the first element
     * 
     * @return callable transducer
     */
    public static function scanT(callable $fnTrans, $initial) : callable
    {
        $accumulator = is_object($initial) ? clone $initial : $initial;
        return fn($step) => function($acc, $v, $k) use($fnTrans, &$accumulator, $step) {
            $accumulator = $fnTrans($accumulator, $v, $k);
            return $step($acc, $accumulator, $k);
        };
    }
    
    /**
     * Like map except that the return of the transformation function is passed
     * into the transformation function as additional parameter for the next 
     * element.
     * 
     * So a bit of map mixed with reduce.
     * 
     * Useful for creating cumulative sequences.
     * 
     * @param callable $fnTrans         transformation function (prev, v, k)
     * @param mixed $initial            the value for prev for the first element
     * @param iterable|null $sequence   input sequence, threadable
     * 
     * @return iterable|callable same type as input, or callable if input 
     * sequence is null
     */
    public static function scan(callable $fnTrans, $initial, ?iterable $sequence = null)
    {
        if($sequence === null)
        {
            return fn($sequence) => self::scan($fnTrans, $initial, $sequence);
        }
        // scan preserves keys, hence defaultStepK
        $out = self::transduce(
            self::scanT($fnTrans, $initial),
            self::defaultStepK($sequence),
            self::emptied($sequence),
            $sequence
        );
        return $out;
    }
}
