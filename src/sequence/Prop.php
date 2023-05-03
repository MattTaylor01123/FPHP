<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait Prop
{
    public static function prop(string $propName, $target)
    {
        if(is_array($target))
        {
            $out = $target[$propName] ?? null;
        }
        else if(is_object($target) && method_exists($target, "prop"))
        {
            $out = $target->prop($propName);
        }
        else if(is_object($target) && method_exists($target, "get"))
        {
            $out = $target->get($propName);
        }
        else if($target instanceof \Traversable)
        {
            $out = null;
            foreach($target as $k => $v)
            {
                if($k === $propName)
                {
                    $out = $v;
                    break;
                }
            }
        }
        else if(is_object($target))
        {
            $out = $target->$propName ?? null;
        }
        else
        {
            throw new InvalidArgumentException("Invalid type for 'target'");
        }
        return $out;
    }
}