<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait First
{
    public static function first($target)
    {
        if(is_object($target) && method_exists($target, "first"))
        {
            return $target->first();
        }
        else if(is_iterable($target))
        {
            $out = null;
            foreach($target as $v)
            {
                $out = $v;
                break;
            }
            return $out;
        }
        else
        {
            throw new InvalidArgumentException("'acc' must be of type array or traversable");
        }
    }
}