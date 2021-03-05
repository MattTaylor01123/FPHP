<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

trait Prop
{
    public static function prop(...$args)
    {
        $prop = self::curry(function($propName, $target) {
            if(is_object($target))
            {
                $out = $target->$propName ?? null;
            }
            else if(is_array($target))
            {
                $out = $target[$propName] ?? null;
            }
            else
            {
                $out = null;
            }
            return $out;
        });
        return $prop(...$args);
    }
}