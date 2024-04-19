<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait RightJoin 
{
    // the iteration order is determined by transduce2, so we can't have a right join
    // transducer
    
    /**
     * Perform an SQL-style right join on two sequences.
     * 
     * Every combination of values in $seq1 and $seq2 is tested against a
     * predicate. If the predicate returns true then the values are combined
     * using a combinator function.
     * 
     * If after testing a value of seq2 against every value in seq1 no values
     * have been found, then seq1 is passed to the combinator function on its
     * own and the result is added to the output sequence.
     * 
     * For each value of seq1, seq2 is iterated fully.
     * 
     * @param callable $fnPred          (v2, v1, k2, k1) => bool
     * @param callable $fnCombinator    (v2, v1 (optional)) => new value
     * @param iterable $seq1            first (outer) sequence
     * @param iterable $seq2            optional, second (inner) sequence, threadable
     * 
     * @return iterable         a new sequence containing all values produced
     * by fnCombinator
     */
    public static function rightJoin(callable $fnPred, callable $fnCombinator, iterable $seq1, ?iterable $seq2 = null) : iterable
    {
        if($seq2 === null)
        {
            return fn(iterable $seq2) => self::rightJoin($fnPred, $fnCombinator, $seq1, $seq2);
        }
        
        return self::transduce2(
            self::leftJoinT2($fnPred, $fnCombinator),
            self::defaultStep($seq1), 
            self::emptied($seq1),
            $seq2, 
            $seq1
        );
    }
}
