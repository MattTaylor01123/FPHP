<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait IterableToArray
{
    // is this function deprecated by inTo ??
    public static function iterableToArray(iterable $it)
    {
        $entries = array();
        $hasKeys = false;
        foreach($it as $k => $v)
        {
            $entries[] = [$v, $k];
            $hasKeys = $hasKeys || ($k !== 0);
        }

        $step = $hasKeys ? fn($acc, $v, $k) => self::assoc($acc, $v, $k)
                         : fn($acc, $v) => self::append($acc, $v);
        $out = self::reduce(fn($acc, $v) => $step($acc, $v[0], $v[1]), [], $entries);
        return $out;
    }
}