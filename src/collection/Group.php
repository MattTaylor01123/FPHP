<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait Group
{
    public static function groupBy(callable $fnGroup, iterable $target)
    {
        return self::groupReduceBy(
            $fnGroup,
            fn($acc, $v) => self::append($acc, $v),
            [],
            $target
        );
    }

    public static function groupMapBy(callable $fnGroup, callable $fnMap, iterable $target)
    {
        return self::groupReduceBy(
            $fnGroup,
            fn($acc, $v, $k) => self::append($acc, $fnMap($v, $k)),
            [],
            $target
        );
    }

    public static function groupReduceBy(callable $fnGroup, callable $fnReduce, $initial, iterable $target)
    {
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
    }
}