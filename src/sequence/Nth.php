<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait Nth
{
    /**
     * Returns the nth element of a collection.
     *
     * if $n is positive then the $nth value is returned.
     * if $n is negative then the length + $nth value is returned.
     * if $n >= length or length + $n < 0 then null is returned
     *
     * @param int $n                index of element in collection to return
     * @param iterable $target      collection to look in
     *
     * @return mixed    the element at the nth position
     */
    public static function nth(int $n, iterable $target)
    {
        $isArr = is_array($target);
        if($n >= 0 && !$isArr)
        {
            $acc = 0;
            $out = null;
            foreach($target as $v)
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
            $vals = $isArr ? $target : iterator_to_array($target, false);
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