<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait Partition
{
    public static function partitionByT(callable $fnGroup, callable $step)
    {
        return self::partitionReduceByT(
            $fnGroup,
            fn($acc, $v) => self::append($acc, $v),
            [],
            $step
        );
    }

    public static function partitionMapByT(callable $fnGroup, callable $fnMap, callable $step)
    {
        return self::partitionReduceByT(
            $fnGroup,
            fn($acc, $v, $k) => self::append($acc, $fnMap($v, $k)),
            [],
            $step
        );
    }

    public static function partitionReduceByT(callable $fnGroup, callable $fnReduce, $initial, callable $step)
    {
        $started = false;
        $grp = null;
        $cache = null;
        return function ($acc, $v, $k) use($fnGroup, $step, &$grp, &$cache, &$started, $fnReduce, $initial) {
            $currGrp = $fnGroup($v, $k);
            if(!$started)
            {
                $started = true;
                $grp = $currGrp;
                $cache = $fnReduce(self::emptied($initial), $v, $k);
                $out = $acc;
            }
            else if($currGrp !== $grp)
            {
                $out = $step($acc, $cache, $grp);
                $cache = $fnReduce(self::emptied($initial), $v, $k);
                $grp = $currGrp;
            }
            else
            {
                $cache = $fnReduce($cache, $v, $k);
                $out = $acc;
            }
            return $out;
        };
    }

    public static function partitionBy(callable $fnGroup, iterable $target)
    {
        return self::transduce(
            self::partitionByT($fnGroup),
            fn($acc, $v, $k) => self::assoc($acc, $v, $k),
            self::emptied($target),
            $target
        );
    }

    public static function partitionReduceBy(callable $fnGroup, callable $fnReducer, $initial, iterable $target)
    {
        return self::transduce(
            fn($step) => self::partitionReduceByT($fnGroup, $fnReducer, $initial, $step),
            fn($acc, $v, $k) => self::assoc($acc, $v, $k),
            self::emptied($target),
            $target
        );
    }

    public static function partitionMapBy(callable $fnGroup, callable $fnMap, iterable $target)
    {
        return self::transduce(
            fn($step) => self::partitionMapByT($fnGroup, $fnMap, $step),
            fn($acc, $v, $k) => self::assoc($acc, $v, $k),
            self::emptied($target),
            $target
        );
    }
}