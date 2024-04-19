<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait LeftJoin 
{    
    /**
     * Transducer (2D) for left join
     * 
     * @param callable $fnPred          (v1, v2, k1, k2) => bool
     * @param callable $fnCombinator    (v1, v2 (optional)) => new value
     * 
     * @return callable transducer (2D)
     */
    public static function leftJoinT2(callable $fnPred, callable $fnCombinator)
    {
        $returnedOuter = false;
        return fn($step2) => self::multiArityfunction(
            // arity-1 - do nothing when flushing outer sequence
            fn($acc1) => $acc1,
            // arity-3 - for left join, return
            function($acc3, $v, $k) use(&$returnedOuter, $fnCombinator, $step2) {
                if(!$returnedOuter)
                {
                    return $step2($acc3, $fnCombinator($v), $k);
                }
                else
                {
                    $returnedOuter = false;
                    return $acc3;
                }
            },
            // arity-5 - do the inner join
            function($acc5, $vl, $vr, $kl, $kr) use(&$returnedOuter, $fnPred, $fnCombinator, $step2) {
                if($fnPred($vl, $vr, $kl, $kr))
                {
                    $returnedOuter = true;
                    return $step2($acc5, $fnCombinator($vl, $vr), $kl);
                }
                else
                {
                    return $acc5;
                }
            }
        );
    }
    
    /**
     * Perform an SQL-style left join on two sequences.
     * 
     * Every combination of values in $seq1 and $seq2 is tested against a
     * predicate. If the predicate returns true then the values are combined
     * using a combinator function.
     * 
     * If after testing a value of seq1 against every value in seq2 no values
     * have been found, then seq1 is passed to the combinator function on its
     * own and the result is added to the output sequence.
     * 
     * For each value of seq1, seq2 is iterated fully.
     * 
     * @param callable $fnPred          (v1, v2, k1, k2) => bool
     * @param callable $fnCombinator    (v1, v2 (optional)) => new value
     * @param iterable $seq1            first (outer) sequence
     * @param iterable $seq2            optional, second (inner) sequence, threadable
     * 
     * @return iterable         a new sequence containing all values produced
     * by fnCombinator
     */
    public static function leftJoin(callable $fnPred, callable $fnCombinator, iterable $seq1, ?iterable $seq2 = null) : iterable
    {
        if($seq2 === null)
        {
            return fn(iterable $seq2) => self::leftJoin($fnPred, $fnCombinator, $seq1, $seq2);
        }
        
        return self::transduce2(
            self::leftJoinT2($fnPred, $fnCombinator),
            self::defaultStep($seq1), 
            self::emptied($seq1),
            $seq1, 
            $seq2
        );
    }
}
