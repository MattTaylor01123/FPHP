<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

trait Accumulators
{
    public static function concat(...$args)
    {
        $concat = self::curry(function($v, array $list) {
            $out = $list;
            $out[] = $v;
            return $out;
        });
        return $concat(...$args);
    }

    public static function concatK(...$args)
    {
        $concatK = self::curry(function($k, $v, array $list) {
            $out = $list;
            $out[$k] = $v;
            return $out;
        });
        return $concatK(...$args);
    }
}