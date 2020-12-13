<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

// based on https://stackoverflow.com/questions/5863128/ordering-of-parameters-to-make-use-of-currying
// Chris Okasaki view, for accumulators, put the most varying argument last, e.g.
// the value.
trait Accumulators
{
    public static function assoc(...$args)
    {
        $assoc = self::curry(function($target, $value, $propName) {
            $out = clone $target;
            $out->$propName = $value;
            return $out;
        });
        return $assoc(...$args);
    }

    public static function concat(...$args)
    {
        $concat = self::curry(function(array $list, $v) {
            $out = $list;
            $out[] = $v;
            return $out;
        });
        return $concat(...$args);
    }

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