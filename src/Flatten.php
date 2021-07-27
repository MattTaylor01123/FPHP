<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

use FPHP\utilities\IterableGenerator;

trait Flatten
{
    public static function flatten(...$args)
    {
        $flatten = self::curry(function(iterable $target) {
            if(is_object($target) && method_exists($target, "flatten"))
            {
                return $target->flatten();
            }
            else
            {
                $generator = function() use($target) {
                    foreach($target as $v)
                    {
                        if(is_iterable($v))
                        {
                            yield from $v;
                        }
                        else
                        {
                            yield $v;
                        }
                    }
                };
                $iterable = new IterableGenerator($generator);
                if(is_array($target))
                {
                    return iterator_to_array($iterable, false);
                }
                else
                {
                    return $iterable;
                }
            }
        });
        return $flatten(...$args);
    }
}