<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

use InvalidArgumentException;
use Traversable;

trait Merge
{
    /*
     * concat but for indexed data structures
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

            if(method_exists($v1, "merge"))
            {
                $out = $v1->merge($v2);
            }
            else if(is_array($v1) && is_array($v2))
            {
                $out = array_merge($v1, $v2);
            }
            else if($v1 instanceof Traversable || is_object($v1))
            {
                $transducer = self::identity();
                $afterFirst = self::transduce($transducer, self::assoc(), self::emptied($v1), $v1);
                $out = self::transduce($transducer, self::assoc(), $afterFirst, $v2);
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