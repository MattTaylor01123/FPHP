<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

trait Props
{
    public static function props(...$args)
    {
        $props = self::curry(function(array $properties, $target) {
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
        });
        return $props(...$args);
    }
}