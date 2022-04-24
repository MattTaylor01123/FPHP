<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait Group
{
    public static function groupBy(...$args)
    {
        $groupBy = self::groupReduceBy(self::__(), self::append(), [], self::__());
        return $groupBy(...$args);
    }

    public static function groupMapBy(...$args)
    {
        $groupMapBy = self::curry(function(callable $fnGroup, callable $fnMap, iterable $target) {
            return self::groupReduceBy($fnGroup, function($acc, $v, $k) use($fnMap) {
                return self::append($acc, $fnMap($v, $k));
            }, [], $target);
        });
        return $groupMapBy(...$args);
    }

    public static function groupReduceBy(...$args)
    {
        $groupReduceBy = self::curry(function(callable $fnGroup, callable $fnReduce, $initial, iterable $target) {
            $out = array();
            foreach($target as $k => $v)
            {
                $g = $fnGroup($v, $k);
                if($g === null)
                {
                    continue;
                }
                if(!self::hasProp($g, $out))
                {
                    $out[$g] = self::emptied($initial);
                }
                $out[$g] = $fnReduce($out[$g], $v, $k);
            }
            return $out;
        });
        return $groupReduceBy(...$args);
    }
}