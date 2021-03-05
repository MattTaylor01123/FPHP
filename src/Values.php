<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

use stdClass;

trait Values
{
    public static function values(...$args)
    {
        $values = self::curry(function($target) {
            if(method_exists($target, "values"))
            {
                return $target->values();
            }
            else if(is_array($target))
            {
                return array_values($target);
            }
            else if($target instanceof stdClass)
            {
                return array_values((array)$target);
            }
            else
            {
                $generator = function() use($target) {
                    foreach($target as $v)
                    {
                        yield $v;
                    }
                };
                return self::generatorToIterable($generator);
            }
        });
        return $values(...$args);
    }
}