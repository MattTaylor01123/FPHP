<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait Props
{
    public static function props(array $properties, $target)
    {
        $out = array();
        foreach($properties as $prop)
        {
            if(self::hasProp($prop, $target))
            {
                $out[] = self::prop($prop, $target);
            }
            else
            {
                $out[] = null;
            }
        }
        return $out;
    }
}