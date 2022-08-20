<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait HasProp
{
    public static function hasProp(string $propName, $target)
    {
        return ((is_object($target) && property_exists($target, $propName)) ||
                (is_array($target) && key_exists($propName, $target)));
    }
}