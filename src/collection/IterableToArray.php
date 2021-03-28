<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait IterableToArray
{
    public static function iterableToArray(...$args)
    {
        $fn = self::curry(function(iterable $it) {
            $entries = array();
            $hasKeys = false;
            foreach($it as $k => $v)
            {
                $entries[] = [$v, $k];
                $hasKeys = $hasKeys || ($k !== 0);
            }

            $step = $hasKeys ? self::assoc() : self::append();
            $out = self::reduce(fn($acc, $v) => $step($acc, $v[0], $v[1]), [], $entries);
            return $out;
        });
        return $fn(...$args);
    }
}