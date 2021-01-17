<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

// based on https://stackoverflow.com/questions/5863128/ordering-of-parameters-to-make-use-of-currying
// Chris Okasaki view, for accumulators, put the most varying argument last, e.g.
// the value.
trait Concat
{
    public static function concat(...$args)
    {
        $concat = self::curry(function(array $list, $v) {
            $out = $list;
            $out[] = $v;
            return $out;
        });
        return $concat(...$args);
    }
}