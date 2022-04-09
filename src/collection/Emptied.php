<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait Emptied
{
    public static function emptied(...$args)
    {
        $emptied = self::curry(function($v) {
            if(is_array($v))
            {
                return [];
            }
            if(is_iterable($v))
            {
                $fn = function() {
                    yield from [];
                };
                $init = self::generatorToIterable($fn);
                return $init;
            }
            if(is_string($v))
            {
                return "";
            }
            if(is_int($v))
            {
                return 0;
            }
            if(is_bool($v))
            {
                return false;
            }
            if(is_float($v))
            {
                return 0.0;
            }
            $class = get_class($v);
            if($class !== false)
            {
                return new $class();
            }
            throw new Exception("Unable to find a suitable empty value for the type of 'v'");
        });
        return $emptied(...$args);
    }
}
