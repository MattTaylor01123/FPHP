<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait Nth
{
    /**
     * Returns the nth element of a sequence.
     *
     * if $n is positive then the $nth value is returned.
     * if $n is negative then the length + $nth value is returned.
     * if $n >= length or length + $n < 0 then null is returned
     *
     * @param int $n                index of element in collection to return
     * @param iterable $seq         optional, sequence, threadable
     *
     * @return mixed    the element at the nth position. If
     * $seq was null then callable.
     */
    public static function nth(int $n, ?iterable $seq = null)
    {
        if($seq === null)
        {
            return fn(iterable $seq) => self::nth($n, $seq);
        }
        
        $isArr = is_array($seq);
        if($n >= 0 && !$isArr)
        {
            $acc = 0;
            $out = null;
            foreach($seq as $v)
            {
                if($acc === $n)
                {
                    $out = $v;
                    break;
                }
                $acc++;
            }
        }
        else
        {
            $vals = $isArr ? $seq : iterator_to_array($seq, false);
            $len = count($vals);
            if($len === 0 || $n >= $len || $len + $n < 0)
            {
                $out = null;
            }
            else
            {
                $idx = ($n >= 0 ? $n : $len + $n);
                $key = array_keys($vals)[$idx];
                $out = $vals[$key];
            }
        }
        return $out;
    }
}