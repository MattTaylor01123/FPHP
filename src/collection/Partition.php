<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait Partition
{
    public static function partitionByT(...$args)
    {
        $partitionByT = self::partitionReduceByT(self::__(), self::append(), [], self::__());
        return $partitionByT(...$args);
    }

    public static function partitionMapByT(...$args)
    {
        $partitionMapByT = self::curry(function(callable $fnGroup, callable $fnMap, callable $step) {
            return self::partitionReduceByT($fnGroup, function($acc, $v, $k) use($fnMap) {
                return self::append($acc, $fnMap($v, $k));
            }, [], $step);
        });
        return $partitionMapByT(...$args);
    }

    public static function partitionReduceByT(...$args)
    {
        $partitionReduceByT = self::curry(function(callable $fnGroup, callable $fnReduce, $initial, callable $step) {
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
        });
        return $partitionReduceByT(...$args);
    }

    public static function partitionBy(...$args)
    {
        $partitionBy = self::curry(function(callable $fnGroup, iterable $target) {
            return self::transduce(self::partitionByT($fnGroup), self::assoc(), self::emptied($target), $target);
        });
        return $partitionBy(...$args);
    }

    public static function partitionReduceBy(...$args)
    {
        $partitionReduceBy = self::curry(function(callable $fnGroup, iterable $target) {
            return self::transduce(self::partitionReduceByT($fnGroup), self::assoc(), self::emptied($target), $target);
        });
        return $partitionReduceBy(...$args);
    }

    public static function partitionMapBy(...$args)
    {
        $partitionMapBy = self::curry(function(callable $fnGroup, iterable $target) {
            return self::transduce(self::partitionMapByT($fnGroup), self::assoc(), self::emptied($target), $target);
        });
        return $partitionMapBy(...$args);
    }
}