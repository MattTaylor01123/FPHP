<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

use stdClass;

trait Keys
{
    public static function keys(...$args)
    {
        $keys = self::curry(function($target) {
            if(method_exists($target, "keys"))
            {
                return $target->keys();
            }
            else if(is_array($target))
            {
                return array_keys($target);
            }
            else if($target instanceof stdClass)
            {
                return array_keys((array)$target);
            }
            else
            {
                $generator = function() use($target) {
                    foreach($target as $k => $v)
                    {
                        yield $k;
                    }
                };
                return self::generatorToIterable($generator);
            }
        });
        return $keys(...$args);
    }
}