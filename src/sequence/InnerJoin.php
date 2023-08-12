<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait InnerJoin 
{
    /**
     * Transducer (2D) for inner join
     * 
     * @param callable $fnPred          (v1, v2, k1, k2) => bool
     * @param callable $fnCombinator    (v1, v2) => new value
     * 
     * @return callable transducer (2D)
     */
    public static function innerJoinT2(callable $fnPred, callable $fnCombinator)
    {
        return fn($step2) => self::multiArityfunction(
            // arity-1 - do nothing when flushing outer sequence
            fn($acc1) => $acc1,
            // arity-3 - for inner join, do nothing when flushing inner sequence
            fn($acc3, $v, $k) => $acc3,
            // arity-5 - do the inner join
            fn($acc5, $vl, $vr, $kl, $kr) =>
                $fnPred($vl, $vr, $kl, $kr) ? $step2($acc5, $fnCombinator($vl, $vr), $kl) : $acc5
        );
    }
    
    /**
     * Perform an SQL-style inner join on two sequences.
     * 
     * Every combination of values in $seq1 and $seq2 is tested against a
     * predicate. If the predicate returns true then the values are combined
     * using a combinator function.
     * 
     * For each value of seq1, seq2 is iterated fully.
     * 
     * @param callable $fnPred          (v1, v2, k1, k2) => bool
     * @param callable $fnCombinator    (v1, v2) => new value
     * @param iterable $seq1            first (outer) sequence
     * @param iterable $seq2            second (inner) sequence
     * 
     * @return iterable         a new sequence containing all values produced
     * by fnCombinator
     */
    public static function innerJoin(callable $fnPred, callable $fnCombinator, iterable $seq1, iterable $seq2) : iterable
    {
        return self::transduce2(
            self::innerJoinT2($fnPred, $fnCombinator),
            self::defaultStep($seq1), 
            self::emptied($seq1),
            $seq1, 
            $seq2
        );
    }
}
