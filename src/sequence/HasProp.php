<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait HasProp
{
    public static function hasProp(string $propName, $target)
    {
        return ((is_object($target) && property_exists($target, $propName)) ||
                (is_array($target) && key_exists($propName, $target)));
    }
}