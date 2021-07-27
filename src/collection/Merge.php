<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

use InvalidArgumentException;

trait Merge
{
    /**
     * Merge two associative arrays or objects together.
     *
     * Does not support generators / traversables as the result would just be a
     * concatenation.
     */
    public static function merge(...$args)
    {
        $merge = self::curry(function($v1, $v2) {
            $v1t = gettype($v1);
            $v2t = gettype($v2);
            $v1type = $v1t === "object" ? get_class($v1) : $v1t;
            $v2type = $v2t === "object" ? get_class($v2) : $v2t;

            if($v1type !== $v2type)
            {
                throw new InvalidArgumentException("v1 and v2 must be of the same type");
            }

            if(is_object($v1) && method_exists($v1, "merge"))
            {
                $out = $v1->merge($v2);
            }
            else if(is_array($v1))
            {
                $out = array_merge($v1, $v2);
            }
            else if(is_object($v1))
            {
                $out = self::reduce(function($acc, $v) {
                    return self::reduce(self::assoc(), $acc, $v);
                }, self::emptied($v1), [$v1, $v2]);
            }
            else
            {
                throw new InvalidArgumentException("v1 and v2 of unhandled type");
            }
            return $out;
        });
        return $merge(...$args);
    }
}