<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

// based on https://stackoverflow.com/questions/5863128/ordering-of-parameters-to-make-use-of-currying
// Chris Okasaki view, for accumulators, put the most varying argument last, e.g.
// the value.
trait ConcatK
{
    public static function concatK(...$args)
    {
        $concatK = self::curry(function(array $list, $v, $k) {
            $out = $list;
            $out[$k] = $v;
            return $out;
        });
        return $concatK(...$args);
    }
}